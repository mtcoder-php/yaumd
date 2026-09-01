/**
 * SCORM 1.2 va SCORM 2004 (3rd/4th Edition) runtime API shim.
 *
 * DOIRA: bitta SCO (murakkab IMS Simple Sequencing/navigation
 * qo'llab-quvvatlanmaydi) — amaliyotda kurs yaratish vositalari
 * (Articulate, iSpring va h.k.) eksport qiladigan paketlarning katta
 * qismi shu tarzda ishlaydi: paket ochiladi, ball/tugallanish/progress
 * kuzatiladi, suspend_data orqali davom ettirish (resume) ishlaydi.
 *
 * SCORM kontenti API obyektini window.parent (yoki window.opener)
 * zanjiri bo'ylab yuqoriga qarab qidiradi — shuning uchun bu obyekt
 * darsni ko'rsatuvchi <iframe>ning ATA (parent) oynasiga o'rnatilishi
 * kerak, iframe src o'rnatilishidan OLDIN (Lesson.vue shunga qarab
 * yozilgan).
 *
 * SCORM 1.2  -> window.API           (LMSInitialize, LMSGetValue, ...)
 * SCORM 2004 -> window.API_1484_11   (Initialize, GetValue, ...)
 */

const ERR_1_2 = {
    NO_ERROR: '0',
    GENERAL_EXCEPTION: '101',
    INVALID_ARGUMENT: '201',
    ELEMENT_CANNOT_HAVE_CHILDREN: '202',
    ELEMENT_NOT_ARRAY: '203',
    NOT_INITIALIZED: '301',
    NOT_IMPLEMENTED: '401',
    INVALID_SET_VALUE: '402',
    READ_ONLY: '403',
    WRITE_ONLY: '404',
    TYPE_MISMATCH: '405',
}

const ERR_2004 = {
    NO_ERROR: '0',
    GENERAL_EXCEPTION: '101',
    ALREADY_INITIALIZED: '103',
    TERMINATED: '104',
    RETRIEVE_BEFORE_INIT: '122',
    RETRIEVE_AFTER_TERM: '123',
    STORE_BEFORE_INIT: '132',
    STORE_AFTER_TERM: '133',
    COMMIT_BEFORE_INIT: '142',
    COMMIT_AFTER_TERM: '143',
    GENERAL_ARGUMENT_ERROR: '201',
    NOT_IMPLEMENTED: '401',
    NOT_INITIALIZED_VALUE: '403',
    READ_ONLY: '404',
    WRITE_ONLY: '405',
    TYPE_MISMATCH: '406',
    VALUE_OUT_OF_RANGE: '407',
}

function parseScorm12Time(str) {
    // "HHHH:MM:SS.SS" — soat qismi 2-4 xonali bo'lishi mumkin
    const m = /^(\d+):(\d{2}):(\d{2}(?:\.\d+)?)$/.exec(String(str || '').trim())
    if (!m) return 0
    return Number(m[1]) * 3600 + Number(m[2]) * 60 + parseFloat(m[3])
}

function formatScorm12Time(totalSeconds) {
    const s = Math.max(0, Math.round(totalSeconds || 0))
    const h = Math.floor(s / 3600)
    const m = Math.floor((s % 3600) / 60)
    const sec = s % 60
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`
}

function parseScorm2004Time(str) {
    // ISO 8601 davomiylik, masalan "PT1H2M3S". Yil/oy/kun qismlari
    // session_time'da amalda deyarli uchramaydi, shuning uchun soddalashtirib
    // faqat soat/daqiqa/soniyani hisobga olamiz.
    const m = /^P(?:\d+Y)?(?:\d+M)?(?:\d+D)?T?(?:(\d+)H)?(?:(\d+)M)?(?:(\d+(?:\.\d+)?)S)?$/.exec(String(str || '').trim())
    if (!m) return 0
    return Number(m[1] || 0) * 3600 + Number(m[2] || 0) * 60 + parseFloat(m[3] || 0)
}

function formatScorm2004Time(totalSeconds) {
    const s = Math.max(0, Math.round(totalSeconds || 0))
    const h = Math.floor(s / 3600)
    const m = Math.floor((s % 3600) / 60)
    const sec = s % 60
    let out = 'PT'
    if (h) out += `${h}H`
    if (m) out += `${m}M`
    out += `${sec}S`
    return out
}

/**
 * Ikkala versiya uchun ham ishlaydigan "indeksli" massiv elementi
 * (masalan cmi.objectives.3.id yoki cmi.interactions.0.result) ustida
 * o'qish/yozish uchun umumiy yordamchi.
 */
function ensureIndex(arr, index) {
    while (arr.length <= index) arr.push({})
    return arr[index]
}

/**
 * @param {'scorm12'|'scorm2004'} version
 * @param {object} initial - serverdan kelgan oldingi urinish (ScormAttempt) ma'lumotlari, resume uchun
 * @param {object} student - { id, name }
 * @param {(payload: object) => Promise<any>} onCommit - serverga saqlash chaqiruvi (axios.post va h.k.)
 * @param {() => void} [onFirstError] - kutilmagan xatoni tashqariga bildirish uchun ixtiyoriy callback
 */
export function createScormRuntime({ version, initial = {}, student = {}, attemptId, onCommit, onError }) {
    const is2004 = version === 'scorm2004'
    const ERR = is2004 ? ERR_2004 : ERR_1_2

    let initialized = false
    let terminated = false
    let lastError = ERR.NO_ERROR
    const sessionStartedAt = Date.now()

    const priorTotalSeconds = Number(initial.total_time || 0)

    // Ichki CMI ma'lumotlar modeli — versiyaga qarab boshlang'ich holat
    const cmi = is2004
        ? {
            completion_status: initial.completion_status || 'incomplete',
            success_status: initial.success_status || 'unknown',
            score: {
                scaled: initial.score_scaled ?? '',
                raw: initial.score_raw ?? '',
                min: initial.score_min ?? '',
                max: initial.score_max ?? '',
            },
            progress_measure: '',
            suspend_data: initial.suspend_data || '',
            entry: priorTotalSeconds > 0 ? 'resume' : 'ab-initio',
            location: '',
            learner_id: String(student.id ?? ''),
            learner_name: student.name || '',
            credit: 'credit',
            mode: 'normal',
            completion_threshold: '',
            max_time_allowed: '',
            time_limit_action: 'continue,no message',
            objectives: Array.isArray(initial.objectives) ? initial.objectives : [],
            interactions: Array.isArray(initial.interactions) ? initial.interactions : [],
        }
        : {
            core: {
                student_id: String(student.id ?? ''),
                student_name: student.name || '',
                lesson_location: '',
                credit: 'credit',
                lesson_status: initial.completion_status === 'completed'
                    ? (initial.success_status === 'passed' ? 'passed' : 'completed')
                    : (initial.success_status === 'failed' ? 'failed' : 'incomplete'),
                entry: priorTotalSeconds > 0 ? 'resume' : 'ab-initio',
                score: {
                    raw: initial.score_raw ?? '',
                    min: initial.score_min ?? '',
                    max: initial.score_max ?? '',
                },
                exit: '',
            },
            suspend_data: initial.suspend_data || '',
            launch_data: '',
            comments: '',
            comments_from_lms: '',
            objectives: Array.isArray(initial.objectives) ? initial.objectives : [],
            student_data: { mastery_score: '', max_time_allowed: '', time_limit_action: '' },
            student_preference: { audio: '', language: '', speed: '', text: '' },
            interactions: Array.isArray(initial.interactions) ? initial.interactions : [],
        }

    function setError(code) {
        lastError = String(code)
        if (code !== ERR.NO_ERROR && typeof onError === 'function') {
            onError(code)
        }
        return code
    }

    function ok(value) {
        lastError = ERR.NO_ERROR
        return value
    }

    // --- indeksli elementlarni ("objectives.3.score.raw" kabi) parse qilish ---
    function matchIndexed(key, prefix) {
        const re = new RegExp(`^${prefix}\\.(\\d+)\\.(.+)$`)
        const m = re.exec(key)
        return m ? { index: Number(m[1]), rest: m[2] } : null
    }

    function get1_2(key) {
        if (key === 'cmi.core.student_id') return ok(cmi.core.student_id)
        if (key === 'cmi.core.student_name') return ok(cmi.core.student_name)
        if (key === 'cmi.core.lesson_location') return ok(cmi.core.lesson_location)
        if (key === 'cmi.core.credit') return ok(cmi.core.credit)
        if (key === 'cmi.core.lesson_status') return ok(cmi.core.lesson_status)
        if (key === 'cmi.core.entry') return ok(cmi.core.entry)
        if (key === 'cmi.core.score.raw') return ok(String(cmi.core.score.raw ?? ''))
        if (key === 'cmi.core.score.min') return ok(String(cmi.core.score.min ?? ''))
        if (key === 'cmi.core.score.max') return ok(String(cmi.core.score.max ?? ''))
        if (key === 'cmi.core.total_time') return ok(formatScorm12Time(priorTotalSeconds))
        if (key === 'cmi.suspend_data') return ok(cmi.suspend_data)
        if (key === 'cmi.launch_data') return ok(cmi.launch_data)
        if (key === 'cmi.comments') return ok(cmi.comments)
        if (key === 'cmi.comments_from_lms') return ok(cmi.comments_from_lms)
        if (key === 'cmi.student_data.mastery_score') return ok(cmi.student_data.mastery_score)
        if (key === 'cmi.student_preference.audio') return ok(cmi.student_preference.audio)
        if (key === 'cmi.student_preference.language') return ok(cmi.student_preference.language)
        if (key === 'cmi.student_preference.speed') return ok(cmi.student_preference.speed)
        if (key === 'cmi.student_preference.text') return ok(cmi.student_preference.text)
        if (key === 'cmi.objectives._count') return ok(String(cmi.objectives.length))
        if (key === 'cmi.interactions._count') return ok(String(cmi.interactions.length))

        const obj = matchIndexed(key, 'cmi\\.objectives')
        if (obj) {
            const item = cmi.objectives[obj.index]
            if (!item) return setError(ERR.GENERAL_EXCEPTION), ''
            if (obj.rest === 'id') return ok(item.id || '')
            if (obj.rest === 'score.raw') return ok(String(item.score_raw ?? ''))
            if (obj.rest === 'score.min') return ok(String(item.score_min ?? ''))
            if (obj.rest === 'score.max') return ok(String(item.score_max ?? ''))
            if (obj.rest === 'status') return ok(item.status || '')
        }

        const inter = matchIndexed(key, 'cmi\\.interactions')
        if (inter) {
            const item = cmi.interactions[inter.index]
            if (!item) return setError(ERR.GENERAL_EXCEPTION), ''
            if (inter.rest === 'id') return ok(item.id || '')
            if (inter.rest === 'type') return ok(item.type || '')
            if (inter.rest === 'result') return ok(item.result || '')
            if (inter.rest === 'student_response') return ok(item.student_response || '')
        }

        setError(ERR.NOT_IMPLEMENTED)
        return ''
    }

    function set1_2(key, value) {
        value = String(value)
        if (key === 'cmi.core.lesson_location') { cmi.core.lesson_location = value; return ok('true') }
        if (key === 'cmi.core.lesson_status') { cmi.core.lesson_status = value; return ok('true') }
        if (key === 'cmi.core.score.raw') { cmi.core.score.raw = value; return ok('true') }
        if (key === 'cmi.core.score.min') { cmi.core.score.min = value; return ok('true') }
        if (key === 'cmi.core.score.max') { cmi.core.score.max = value; return ok('true') }
        if (key === 'cmi.core.exit') { cmi.core.exit = value; return ok('true') }
        if (key === 'cmi.core.session_time') { cmi.core.session_time = value; return ok('true') }
        if (key === 'cmi.suspend_data') { cmi.suspend_data = value; return ok('true') }
        if (key === 'cmi.comments') { cmi.comments = value; return ok('true') }
        if (key === 'cmi.student_preference.audio') { cmi.student_preference.audio = value; return ok('true') }
        if (key === 'cmi.student_preference.language') { cmi.student_preference.language = value; return ok('true') }
        if (key === 'cmi.student_preference.speed') { cmi.student_preference.speed = value; return ok('true') }
        if (key === 'cmi.student_preference.text') { cmi.student_preference.text = value; return ok('true') }

        // Faqat o'qish uchun elementlarga yozishga urinish
        if ([
            'cmi.core.student_id', 'cmi.core.student_name', 'cmi.core.credit',
            'cmi.core.entry', 'cmi.core.total_time', 'cmi.launch_data',
            'cmi.comments_from_lms', 'cmi.student_data.mastery_score',
        ].includes(key)) {
            return setError(ERR.READ_ONLY), 'false'
        }

        const obj = matchIndexed(key, 'cmi\\.objectives')
        if (obj) {
            const item = ensureIndex(cmi.objectives, obj.index)
            if (obj.rest === 'id') item.id = value
            else if (obj.rest === 'score.raw') item.score_raw = value
            else if (obj.rest === 'score.min') item.score_min = value
            else if (obj.rest === 'score.max') item.score_max = value
            else if (obj.rest === 'status') item.status = value
            else return setError(ERR.NOT_IMPLEMENTED), 'false'
            return ok('true')
        }

        const inter = matchIndexed(key, 'cmi\\.interactions')
        if (inter) {
            const item = ensureIndex(cmi.interactions, inter.index)
            if (inter.rest === 'id') item.id = value
            else if (inter.rest === 'type') item.type = value
            else if (inter.rest === 'result') item.result = value
            else if (inter.rest === 'student_response') item.student_response = value
            else if (inter.rest === 'weighting') item.weighting = value
            else if (inter.rest === 'latency') item.latency = value
            else if (inter.rest.startsWith('correct_responses')) { /* qabul qilinadi, saqlanmaydi (sequencing yo'q) */ }
            else return setError(ERR.NOT_IMPLEMENTED), 'false'
            return ok('true')
        }

        setError(ERR.NOT_IMPLEMENTED)
        return 'false'
    }

    function get2004(key) {
        if (key === 'cmi.completion_status') return ok(cmi.completion_status)
        if (key === 'cmi.success_status') return ok(cmi.success_status)
        if (key === 'cmi.score.scaled') return ok(String(cmi.score.scaled ?? ''))
        if (key === 'cmi.score.raw') return ok(String(cmi.score.raw ?? ''))
        if (key === 'cmi.score.min') return ok(String(cmi.score.min ?? ''))
        if (key === 'cmi.score.max') return ok(String(cmi.score.max ?? ''))
        if (key === 'cmi.progress_measure') return ok(String(cmi.progress_measure ?? ''))
        if (key === 'cmi.suspend_data') return ok(cmi.suspend_data)
        if (key === 'cmi.entry') return ok(cmi.entry)
        if (key === 'cmi.location') return ok(cmi.location)
        if (key === 'cmi.learner_id') return ok(cmi.learner_id)
        if (key === 'cmi.learner_name') return ok(cmi.learner_name)
        if (key === 'cmi.credit') return ok(cmi.credit)
        if (key === 'cmi.mode') return ok(cmi.mode)
        if (key === 'cmi.completion_threshold') return ok(cmi.completion_threshold)
        if (key === 'cmi.max_time_allowed') return ok(cmi.max_time_allowed)
        if (key === 'cmi.time_limit_action') return ok(cmi.time_limit_action)
        if (key === 'cmi.total_time') return ok(formatScorm2004Time(priorTotalSeconds))
        if (key === 'cmi.objectives._count') return ok(String(cmi.objectives.length))
        if (key === 'cmi.interactions._count') return ok(String(cmi.interactions.length))
        if (key === 'adl.nav.request') return ok('')

        const obj = matchIndexed(key, 'cmi\\.objectives')
        if (obj) {
            const item = cmi.objectives[obj.index]
            if (!item) return setError(ERR.GENERAL_ARGUMENT_ERROR), ''
            if (obj.rest === 'id') return ok(item.id || '')
            if (obj.rest === 'score.scaled') return ok(String(item.score_scaled ?? ''))
            if (obj.rest === 'score.raw') return ok(String(item.score_raw ?? ''))
            if (obj.rest === 'score.min') return ok(String(item.score_min ?? ''))
            if (obj.rest === 'score.max') return ok(String(item.score_max ?? ''))
            if (obj.rest === 'success_status') return ok(item.success_status || 'unknown')
            if (obj.rest === 'completion_status') return ok(item.completion_status || 'unknown')
            if (obj.rest === 'progress_measure') return ok(String(item.progress_measure ?? ''))
        }

        const inter = matchIndexed(key, 'cmi\\.interactions')
        if (inter) {
            const item = cmi.interactions[inter.index]
            if (!item) return setError(ERR.GENERAL_ARGUMENT_ERROR), ''
            if (inter.rest === 'id') return ok(item.id || '')
            if (inter.rest === 'type') return ok(item.type || '')
            if (inter.rest === 'result') return ok(item.result || '')
            if (inter.rest === 'learner_response') return ok(item.learner_response || '')
            if (inter.rest === 'description') return ok(item.description || '')
        }

        setError(ERR.NOT_IMPLEMENTED)
        return ''
    }

    function set2004(key, value) {
        value = String(value)
        if (key === 'cmi.completion_status') { cmi.completion_status = value; return ok('true') }
        if (key === 'cmi.success_status') { cmi.success_status = value; return ok('true') }
        if (key === 'cmi.score.scaled') { cmi.score.scaled = value; return ok('true') }
        if (key === 'cmi.score.raw') { cmi.score.raw = value; return ok('true') }
        if (key === 'cmi.score.min') { cmi.score.min = value; return ok('true') }
        if (key === 'cmi.score.max') { cmi.score.max = value; return ok('true') }
        if (key === 'cmi.progress_measure') { cmi.progress_measure = value; return ok('true') }
        if (key === 'cmi.suspend_data') { cmi.suspend_data = value; return ok('true') }
        if (key === 'cmi.exit') { cmi.exit = value; return ok('true') }
        if (key === 'cmi.session_time') { cmi.session_time = value; return ok('true') }
        if (key === 'cmi.location') { cmi.location = value; return ok('true') }
        if (key === 'adl.nav.request') { return ok('true') } // sequencing yo'q — qabul qilinadi, e'tiborga olinmaydi

        if ([
            'cmi.entry', 'cmi.learner_id', 'cmi.learner_name', 'cmi.credit',
            'cmi.mode', 'cmi.completion_threshold', 'cmi.max_time_allowed',
            'cmi.time_limit_action', 'cmi.total_time',
        ].includes(key)) {
            return setError(ERR.READ_ONLY), 'false'
        }

        const obj = matchIndexed(key, 'cmi\\.objectives')
        if (obj) {
            const item = ensureIndex(cmi.objectives, obj.index)
            if (obj.rest === 'id') item.id = value
            else if (obj.rest === 'score.scaled') item.score_scaled = value
            else if (obj.rest === 'score.raw') item.score_raw = value
            else if (obj.rest === 'score.min') item.score_min = value
            else if (obj.rest === 'score.max') item.score_max = value
            else if (obj.rest === 'success_status') item.success_status = value
            else if (obj.rest === 'completion_status') item.completion_status = value
            else if (obj.rest === 'progress_measure') item.progress_measure = value
            else return setError(ERR.NOT_IMPLEMENTED), 'false'
            return ok('true')
        }

        const inter = matchIndexed(key, 'cmi\\.interactions')
        if (inter) {
            const item = ensureIndex(cmi.interactions, inter.index)
            if (inter.rest === 'id') item.id = value
            else if (inter.rest === 'type') item.type = value
            else if (inter.rest === 'result') item.result = value
            else if (inter.rest === 'learner_response') item.learner_response = value
            else if (inter.rest === 'description') item.description = value
            else if (inter.rest === 'weighting') item.weighting = value
            else if (inter.rest === 'timestamp') item.timestamp = value
            else return setError(ERR.NOT_IMPLEMENTED), 'false'
            return ok('true')
        }

        setError(ERR.NOT_IMPLEMENTED)
        return 'false'
    }

    function currentSessionSeconds() {
        return Math.round((Date.now() - sessionStartedAt) / 1000)
    }

    async function commit() {
        const sessionSeconds = currentSessionSeconds()

        const payload = is2004
            ? {
                attempt_id: attemptId,
                completion_status: cmi.completion_status,
                success_status: cmi.success_status,
                score_raw: cmi.score.raw !== '' ? Number(cmi.score.raw) : null,
                score_min: cmi.score.min !== '' ? Number(cmi.score.min) : null,
                score_max: cmi.score.max !== '' ? Number(cmi.score.max) : null,
                score_scaled: cmi.score.scaled !== '' ? Number(cmi.score.scaled) : null,
                session_time: sessionSeconds,
                suspend_data: cmi.suspend_data,
                interactions: cmi.interactions,
                objectives: cmi.objectives,
            }
            : {
                attempt_id: attemptId,
                completion_status: cmi.core.lesson_status === 'completed' || cmi.core.lesson_status === 'passed' ? 'completed' : 'incomplete',
                success_status: cmi.core.lesson_status === 'passed' ? 'passed' : (cmi.core.lesson_status === 'failed' ? 'failed' : 'unknown'),
                score_raw: cmi.core.score.raw !== '' ? Number(cmi.core.score.raw) : null,
                score_min: cmi.core.score.min !== '' ? Number(cmi.core.score.min) : null,
                score_max: cmi.core.score.max !== '' ? Number(cmi.core.score.max) : null,
                session_time: sessionSeconds,
                suspend_data: cmi.suspend_data,
                interactions: cmi.interactions,
                objectives: cmi.objectives,
            }

        if (typeof onCommit === 'function') {
            try {
                await onCommit(payload)
            } catch (e) {
                setError(ERR.GENERAL_EXCEPTION)
                return false
            }
        }
        return true
    }

    const errorStrings12 = {
        [ERR_1_2.NO_ERROR]: 'No error',
        [ERR_1_2.GENERAL_EXCEPTION]: 'General exception',
        [ERR_1_2.INVALID_ARGUMENT]: "Invalid argument error",
        [ERR_1_2.NOT_INITIALIZED]: 'Not initialized',
        [ERR_1_2.NOT_IMPLEMENTED]: 'Not implemented error',
        [ERR_1_2.READ_ONLY]: 'Element is read only',
        [ERR_1_2.WRITE_ONLY]: 'Element is write only',
        [ERR_1_2.TYPE_MISMATCH]: 'Incorrect data type',
    }
    const errorStrings2004 = {
        [ERR_2004.NO_ERROR]: 'No Error',
        [ERR_2004.GENERAL_EXCEPTION]: 'General Exception',
        [ERR_2004.ALREADY_INITIALIZED]: 'Already Initialized',
        [ERR_2004.TERMINATED]: 'Content Instance Terminated',
        [ERR_2004.NOT_IMPLEMENTED]: 'Undefined Data Model Element',
        [ERR_2004.READ_ONLY]: 'Data Model Element Is Read Only',
        [ERR_2004.WRITE_ONLY]: 'Data Model Element Is Write Only',
        [ERR_2004.TYPE_MISMATCH]: 'Data Model Element Type Mismatch',
    }

    if (is2004) {
        return {
            Initialize(_) {
                if (initialized) return setError(ERR_2004.ALREADY_INITIALIZED), 'false'
                if (terminated) return setError(ERR_2004.TERMINATED), 'false'
                initialized = true
                return ok('true')
            },
            Terminate(_) {
                if (!initialized) return setError(ERR_2004.COMMIT_BEFORE_INIT), 'false'
                if (terminated) return setError(ERR_2004.TERMINATED), 'false'
                terminated = true
                commit()
                return ok('true')
            },
            GetValue(key) {
                if (!initialized) return setError(ERR_2004.RETRIEVE_BEFORE_INIT), ''
                if (terminated) return setError(ERR_2004.RETRIEVE_AFTER_TERM), ''
                return get2004(key)
            },
            SetValue(key, value) {
                if (!initialized) return setError(ERR_2004.STORE_BEFORE_INIT), 'false'
                if (terminated) return setError(ERR_2004.STORE_AFTER_TERM), 'false'
                return set2004(key, value)
            },
            Commit(_) {
                if (!initialized) return setError(ERR_2004.COMMIT_BEFORE_INIT), 'false'
                if (terminated) return setError(ERR_2004.COMMIT_AFTER_TERM), 'false'
                commit()
                return ok('true')
            },
            GetLastError() { return lastError },
            GetErrorString(code) { return errorStrings2004[String(code)] || 'Unknown error' },
            GetDiagnostic(code) { return errorStrings2004[String(code)] || '' },
        }
    }

    return {
        LMSInitialize(_) {
            if (initialized) return setError(ERR_1_2.GENERAL_EXCEPTION), 'false'
            initialized = true
            return ok('true')
        },
        LMSFinish(_) {
            if (!initialized) return setError(ERR_1_2.NOT_INITIALIZED), 'false'
            terminated = true
            commit()
            return ok('true')
        },
        LMSGetValue(key) {
            if (!initialized) return setError(ERR_1_2.NOT_INITIALIZED), ''
            return get1_2(key)
        },
        LMSSetValue(key, value) {
            if (!initialized) return setError(ERR_1_2.NOT_INITIALIZED), 'false'
            return set1_2(key, value)
        },
        LMSCommit(_) {
            if (!initialized) return setError(ERR_1_2.NOT_INITIALIZED), 'false'
            commit()
            return ok('true')
        },
        LMSGetLastError() { return lastError },
        LMSGetErrorString(code) { return errorStrings12[String(code)] || 'Unknown error' },
        LMSGetDiagnostic(code) { return errorStrings12[String(code)] || '' },
    }
}
