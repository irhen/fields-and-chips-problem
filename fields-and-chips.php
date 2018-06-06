<?php

if (!isset($argv[1]) && !isset($argv[2])) { die("Слишком мало аргументов.\r\n"); }

if (!is_numeric($argv[1]) || !is_numeric($argv[2])) { die("Все аргументы должны быть целочисленными.\r\n"); }

$fieldsCount = (int)$argv[1];
$chipCount = (int)$argv[2];

if ($fieldsCount < $chipCount) { die("Не может быть. Может, числа нужно было ввести в другом порядке?\r\n"); }

const CHIP = '$';
const NEW_LINE_DELIMITER = "\r\n";

const DESTINATION = './';
const FILE = 'result.txt';
const LESS_THAN_10_OPTIONS = "Менее 10 вариантов";

function factorial($num) {
    if ($num === 0 || $num === 1) { return 1; }
    return $num * factorial($num - 1);
}

function countTotalPermutations($tokens, $positions) {
    $top = factorial($positions);
    $bottomFirst = factorial($tokens);
    $bottomSecond = factorial($positions - $tokens);

    return $top / ($bottomFirst * $bottomSecond);
}

$memo = [];
function memoPermute($tokens, $positions) {
    global $memo;
    $memoKey = "{$tokens}_{$positions}";

    if (isset($memo[$memoKey])) {
        return $memo[$memoKey];
    }

    $result = permute($tokens, $positions);
    $memo[$memoKey] = $result;

    return $result;
}

function permute($tokens, $positions) {
    if ($positions < $tokens) { return []; }
    if ($tokens == 0) { return [str_repeat('  ', $positions)]; }

    $spaces = '';
    $result = [];

    for ($i = 0; $i < $positions; $i++) {
        $spacesWithToken = $spaces . CHIP;

        foreach (memoPermute($tokens - 1, $positions - $i - 1) as $item) {
            $result[] = $spacesWithToken . $item;
        }

        $spaces .= ' ';
    }

    return $result;
}

function show($count, $fieldsCount, $data)
{
    if ($count < 10) {
        return LESS_THAN_10_OPTIONS . NEW_LINE_DELIMITER;
    }

    $result = [];

    $counted = $count . ' вариантов найдено.' . NEW_LINE_DELIMITER;
    $fields = '';
    for ($i = 1; $i <= $fieldsCount; $i++) {
        $fields .= "{$i} ";
    }

    $result[] = $counted;
    $result[] = $fields;

    foreach ($data as $mutation) {
        $item = \str_split($mutation);
        $item = array_map(
            function ($k, $v) { return $v . \str_repeat(' ', \strlen((string)($k + 1))); },
            array_keys($item),
            array_values($item)
        );
        $result[] = implode('', $item);
    }

    return implode(NEW_LINE_DELIMITER, $result);
}

$data = permute($chipCount, $fieldsCount);
$counted = countTotalPermutations($chipCount, $fieldsCount);
$dataForFile = show($counted, $fieldsCount, $data);

$open = touch(DESTINATION . FILE);
if (!$open) { die('Не получилось открыть файл ' . FILE . ", скрипт остановлен." . NEW_LINE_DELIMITER); }

$written = file_put_contents(DESTINATION . FILE, $dataForFile);
if (!$written) { die('Не получилось записать данные в файл ' . FILE . ", скрипт остановлен." . NEW_LINE_DELIMITER); }

echo('Готово!' . NEW_LINE_DELIMITER);
