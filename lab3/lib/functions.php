<?php

function sanitizeText($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function timeAgo($date)
{
    $seconds = time() - strtotime($date);

    if ($seconds < 60) {
        return 'щойно';
    }

    if ($seconds < 3600) {
        return floor($seconds / 60) . ' хв тому';
    }

    if ($seconds < 86400) {
        return floor($seconds / 3600) . ' год тому';
    }

    return floor($seconds / 86400) . ' дн тому';
}
