<?php

declare(strict_types=1);

return [
    'sizes' => [
        'easy' => 10,
        'medium' => 12,
        'hard' => 15,
    ],
    'alphabet' => range('A', 'Z'),
    'allowDiagonals' => [
        'easy' => false,
        'medium' => true,
        'hard' => true,
    ],
    'allowReverse' => [
        'easy' => false,
        'medium' => true,
        'hard' => true,
    ],
    'maxWordLen' => 18,
    'directions' => [
        'horizontal' => [0, 1],
        'vertical' => [1, 0],
        'diagonal_down' => [1, 1],
        'diagonal_up' => [-1, 1],
    ],
];
