<?php

declare(strict_types=1);

$mode = $argv[1] ?? '--staged';

function gitOutput(string $command): string
{
    $nullDevice = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
    $result = shell_exec('git '.$command.' 2>'.$nullDevice);

    return is_string($result) ? $result : '';
}

function isSensitivePath(string $path): bool
{
    $name = basename(str_replace('\\', '/', $path));

    if ($name === '.env.example') {
        return false;
    }

    return preg_match('/^\.env(?:\..*)?$/i', $name) === 1
        || strcasecmp($name, 'portgen-secrets.env') === 0
        || str_ends_with(strtolower($name), '.pem');
}

function hasSecret(string $line): bool
{
    $patterns = [
        '/\beyJ[A-Za-z0-9_-]{16,}\.[A-Za-z0-9_-]{8,}\.[A-Za-z0-9_-]{8,}\b/',
        '/\b(?:gh[pousr]_[A-Za-z0-9]{20,}|github_pat_[A-Za-z0-9_]{20,}|sk-(?:live|proj|ant)-[A-Za-z0-9_-]{12,}|sb_secret_[A-Za-z0-9_-]{16,}|rnd_[A-Za-z0-9]{20,})\b/',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $line) === 1) {
            return true;
        }
    }

    if (preg_match('~\b(?:SUPABASE_PASSWORD|(?:SUPABASE_)?(?:DB|DATABASE)_PASSWORD)\s*[:=]\s*["\']?([^"\'\s#]+)~i', $line, $matches) === 1) {
        $value = strtolower($matches[1]);
        $placeholder = preg_match('/^(?:$|\$|<|\*|your(?:_|-)|change(?:_|-)?me|replace(?:_|-)?me|placeholder)/i', $value) === 1;

        return ! $placeholder;
    }

    if (preg_match('~(?:postgres(?:ql)?://[^:\s/@]+:([^@\s]+)@[^/\s]*\.supabase(?:\.co)?)(?:/|\b)~i', $line, $matches) === 1) {
        $value = strtolower($matches[1]);
        if (preg_match('/^(?:your(?:_|-)|change(?:_|-)?me|replace(?:_|-)?me|placeholder|<|\$|\*)/i', $value) !== 1) {
            return true;
        }
    }

    return preg_match('~\b(?:api[_-]?key|access[_-]?token|secret[_-]?key|auth[_-]?token)\s*[:=]\s*["\']?([A-Za-z0-9_\-./+=]{24,})~i', $line) === 1;
}

$paths = [];
$contents = '';

if ($mode === '--staged') {
    $paths = array_filter(explode("\n", trim(gitOutput('diff --cached --name-only --diff-filter=ACMR'))));
    $contents = gitOutput('diff --cached --unified=0 --no-ext-diff --no-color --');
} elseif ($mode === '--worktree') {
    $paths = array_filter(explode("\0", gitOutput('ls-files -z')));
    foreach ($paths as $path) {
        if (! isSensitivePath($path) && is_file($path)) {
            $contents .= file_get_contents($path)."\n";
        }
    }
} else {
    fwrite(STDERR, "Usage: php scripts/check-secrets.php [--staged|--worktree]\n");
    exit(2);
}

$failures = [];
foreach ($paths as $path) {
    if (isSensitivePath($path)) {
        $failures[] = "Sensitive filename is staged or tracked: {$path}";
    }
}

foreach (explode("\n", $contents) as $lineNumber => $line) {
    if ($mode === '--staged' && ! str_starts_with($line, '+')) {
        continue;
    }
    if ($mode === '--staged') {
        $line = substr($line, 1);
    }
    if (hasSecret($line)) {
        $failures[] = 'Potential credential detected in changed content at line '.($lineNumber + 1).'.';
    }
}

if ($failures !== []) {
    fwrite(STDERR, implode("\n", array_unique($failures))."\nRemove the credential and rotate it if it was exposed.\n");
    exit(1);
}

fwrite(STDOUT, "Secret scan passed.\n");
