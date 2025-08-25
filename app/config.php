<?php

declare(strict_types=1);

return [
    'game' => [
        'directions' => [
            'horizontal' => [0, 1],
            'vertical' => [1, 0],
            'diagonal_down' => [1, 1],
            'diagonal_up' => [-1, 1]
        ],
        'maxWordLen' => 12, // Maximum word length (60% rule is now enforced in PuzzleGenerator)
        'alphabet' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ ',
        'difficulties' => [
            'easy' => ['size' => 10, 'diagonals' => false, 'reverse' => false],
            'medium' => ['size' => 15, 'diagonals' => true, 'reverse' => false],
            'hard' => ['size' => 20, 'diagonals' => true, 'reverse' => true]
        ]
    ],
    'paths' => [
        'storage' => __DIR__ . '/../storage',
        'resources' => __DIR__ . '/../resources'
    ],
    'jwt' => [
        'expiry' => (int)($_ENV['JWT_EXPIRY'] ?? 3600),
        'secret' => $_ENV['JWT_SECRET'] ?? 'default-secret-change-in-production'
    ]
];
