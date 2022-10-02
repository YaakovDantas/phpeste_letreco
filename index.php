<?php

require_once(__DIR__ . '/vendor/autoload.php');

require_once('config.php');

$board = 'letreco';
$game = getopt(null, ["game:"]);
if (count($game)) {
  $board = $game['game'];
}

$mode = getopt(null, ["tryhard:"]);
$isTryHard = isset($mode['tryhard']) && $mode['tryhard'] == 'on'  ? true: false;

$word = new Word();
$wordSearch = new WordSearch($word);
$badWords = new BadWords();

$board_options = [
  'term' => new BoardTerm($word),
  'letreco' => new BoardLetreco($word),
];

$board = $board_options[$board];

$game = new Game($board, $badWords, $word, $wordSearch, $isTryHard);
$game->play();

echo PHP_EOL;

echo '-----------' . PHP_EOL;
echo '-GAME OVER-' . PHP_EOL;
echo '-----------' . PHP_EOL;

echo PHP_EOL;
if ($game->checkWin()) {

  $wordOfTheDay = strtoupper($game->getLastGuessedWord());
  $game->finishHim($game->getLastGuessedWord());
  echo '---------------------------' . PHP_EOL;
  echo "- PALAVRA DO DIA : $wordOfTheDay -" . PHP_EOL;
  echo '---------------------------' . PHP_EOL;
  
} else {
  echo '---------------------------' . PHP_EOL;
  echo "---------- PERDEU ---------" . PHP_EOL;
  echo '---------------------------' . PHP_EOL;
  $game->finish();
}
echo PHP_EOL;

