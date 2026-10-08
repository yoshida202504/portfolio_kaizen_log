<?php

namespace App\Services;

class CommentSafetyChecker
{
    /**
     * A deliberately small, maintainable first-pass list for clear harmful expressions.
     * Contextual moderation remains a known limitation and is documented in the README.
     *
     * @var list<string>
     */
    private const HARMFUL_EXPRESSIONS = [
        '死ね',
        '殺す',
        '消えろ',
        'くたばれ',
        'レイプ',
    ];

    public function mayHarmUsers(string $body): bool
    {
        $normalizedBody = $this->normalize($body);

        foreach (self::HARMFUL_EXPRESSIONS as $expression) {
            if (str_contains($normalizedBody, $expression)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $body): string
    {
        return mb_strtolower(
            str_replace([' ', '　', "\n", "\r", "\t"], '', $body),
            'UTF-8',
        );
    }
}
