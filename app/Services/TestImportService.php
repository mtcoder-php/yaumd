<?php

namespace App\Services;

use App\Models\TestQuestion;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\IOFactory;

class TestImportService
{
    public function import(UploadedFile $file, int $subjectId, string $language): int
    {
        $text = $file->getClientOriginalExtension() === 'txt'
            ? file_get_contents($file->getRealPath())
            : $this->extractFromDocx($file);

        $questions = $this->parse($text);

        foreach ($questions as $q) {
            TestQuestion::create([
                'subject_id'     => $subjectId,
                'language'       => $language,
                'question'       => $q['question'],
                'option_a'       => $q['option_a'],
                'option_b'       => $q['option_b'],
                'option_c'       => $q['option_c'],
                'option_d'       => $q['option_d'],
                'correct_answer' => $q['correct_answer'],
                'is_active'      => true,
            ]);
        }

        return count($questions);
    }

    public function template(): string
    {
        return implode("\n", [
            "1.Alisher Navoiy qachon tavallud topgan?",
            "- 1501", "- 1336", "# 1441", "- 1440", "",
            "2.O'zbekiston mustaqillikka qachon erishgan?",
            "- 1990", "# 1991", "- 1992", "- 1993", "",
            "3.Toshkent shahri qachon poytaxt bo'lgan?",
            "- 1920", "# 1930", "- 1940", "- 1950", "",
            "4.Amir Temur nechanchi yilda tug'ilgan?",
            "- 1337", "# 1336", "- 1340", "- 1350", "",
            "5.O'zbek tili qaysi oilaga mansub?",
            "- Slavyan", "# Turkiy", "- Arab", "- Fors",
        ]);
    }

    private function extractFromDocx(UploadedFile $file): string
    {
        $phpWord = IOFactory::load($file->getRealPath());
        $text    = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                } elseif (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $child) {
                        if (method_exists($child, 'getText')) {
                            $text .= $child->getText();
                        }
                    }
                    $text .= "\n";
                }
            }
        }

        return $text;
    }

    private function parse(string $text): array
    {
        $questions = [];
        $lines     = array_values(array_filter(array_map('trim', explode("\n", $text))));

        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];

            if (preg_match('/^\d+[\.\)]\s*(.+)/', $line, $m)) {
                $question = trim($m[1]);
                $options  = [];
                $correct  = null;
                $i++;

                while ($i < count($lines) && count($options) < 4) {
                    $optLine = $lines[$i];
                    if (preg_match('/^#\s*(.+)/', $optLine, $om)) {
                        $correct   = count($options);
                        $options[] = trim($om[1]);
                    } elseif (preg_match('/^[-–]\s*(.+)/', $optLine, $om)) {
                        $options[] = trim($om[1]);
                    } elseif (preg_match('/^\d+[\.\)]\s*/', $optLine)) {
                        break;
                    }
                    $i++;
                }

                if (count($options) === 4 && $correct !== null) {
                    $letters     = ['a', 'b', 'c', 'd'];
                    $questions[] = [
                        'question'       => $question,
                        'option_a'       => $options[0],
                        'option_b'       => $options[1],
                        'option_c'       => $options[2],
                        'option_d'       => $options[3],
                        'correct_answer' => $letters[$correct],
                    ];
                }
            } else {
                $i++;
            }
        }

        return $questions;
    }
}
