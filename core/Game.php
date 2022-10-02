<?php

class Game {
	private $board;
	private $badWords;
	private $word;
	private $wordSearch;
	private $guess;
	private $round;
	private $rightWords;
	private $missPlacedWords;
	private $wrongsWords;
	private $tryHard;

	function __construct($board, BadWords $badWords, Word $word, WordSearch $wordSearch, $tryHard = false) {
		$this->board = $board;
		$this->badWords = $badWords;
		$this->word = $word;
		$this->wordSearch = $wordSearch;

		$this->guess = [
			'1' => 'rosea',
			'2' => 'mundi',
		];

		$this->readGuessPosition = 1;
		$this->round = 1;
		$this->tryHard = $tryHard;
		$this->resetGuess = true;
		$this->incrementRound = true;

		$this->rightWords = [];
		$this->missPlacedWords = [];
		$this->wrongsWords = [];
		$this->missplaceUsed = [];
		$this->guessWordDone = [];
	}

	public function play($isTryHard = false)
	{

		$this->start($isTryHard);

		if (!$this->board->checkWinGame() && $this->tryHard) {
			$this->finish();
			$this->readGuessPosition = 1;
			$this->waitBoardLoad(4000000);
			$this->play($this->tryHard);
		}
	}

	public function finish()
	{
		$this->board->close();
	}

	private function start($isTryHard = false)
	{
		$this->board->open();
		$this->board->unlockBoard();

		while ($this->board->checkGameOver()) {
			$round_word = $this->guess[$this->round];
			$this->board->makeGuess($round_word);
			$this->waitBoardLoad();

			$this->checkGuessIsAcceptable($round_word);

			if (!$this->board->checkGameOver()) {
				break;
			}

			$this->guessWordDone[] = $round_word;

			$this->checkHasRights();
			$this->checkHasMissPlaced();
			$this->checkHasWrongs();

			$this->incrementRound();

			if ($this->round > 2) {
				$nextRoundWord = false;
				while($nextRoundWord == false) {
					$nextRoundWordGuess = $this->word->makeNextGuess(
						$this->rightWords,
						$this->missPlacedWords,
						$this->missplaceUsed,
						$this
					);

					$nextRoundWord = $this->wordSearch->find(
						$nextRoundWordGuess,
						$this->badWords->getList(),
						$this->guessWordDone,
						$this->wrongsWords
					);
				}

				$this->guess[$this->round] = strtolower($this->word->clean($nextRoundWord));
			}
		}
	}

	public function getLastGuessedWord()
	{
		return end($this->guess);
	}

	public function setMissplaceUsed($key, $value)
	{
		if (!array_key_exists($key, $this->missplaceUsed)) {
			$this->missplaceUsed[$key][] = $value;
		}
		if (!in_array($value, $this->missplaceUsed[$key])) {
			$this->missplaceUsed[$key][] = $value;
		}
	}

	private function checkHasRights()
	{
		for ($i=1; $i < 6; $i++) {
			$isRightWord = $this->board->checkWordIsRightPlace($this->readGuessPosition, $i);
			if ($isRightWord) {
				$word = $this->board->getWord($this->readGuessPosition, $i);
				$this->rightWords[$i] = $word;

				if (in_array($word, $this->rightWords)) {
					if (
							(array_key_exists($word, $this->missPlacedWords)) !== false
					) {
							unset($this->missPlacedWords[$word]);
					}

					if ((array_key_exists($word, $this->missplaceUsed)) !== false) {
							unset($this->missplaceUsed[$word]);
					}
				}
			}
		}
	}

	private function checkHasMissPlaced()
	{
		for ($i=1; $i < 6; $i++) {
			$isMissedWord = $this->board->checkWordIsMissPlaced($this->readGuessPosition, $i);
			if ($isMissedWord) {
				$word = $this->board->getWord($this->readGuessPosition, $i);
				if (
					(!in_array($word, $this->rightWords)) !== false
					&&
					!array_key_exists($word, $this->missPlacedWords)
				) {
					$this->missPlacedWords[$word][] = $i;
				}
			}
		}
	}

	private function checkHasWrongs()
	{
		for ($i=1; $i < 6; $i++) {
			$isWrongWord = $this->board->checkWordIsWrong($this->readGuessPosition, $i);
			if ($isWrongWord) {
				$word = $this->board->getWord($this->readGuessPosition, $i);
				$this->wrongsWords[] = $word;
			}
		}
	}

	private function checkGuessIsAcceptable($roundWord)
	{
		$checkAlertIsShow = $this->board->hasAlertOnBoard();
		if ($checkAlertIsShow) {
			$this->setResetGuess(false);
			$this->setIncrementRound(false);
			$this->badWords->addWord($roundWord);
			for ($i=1; $i < 6; $i++) {
				$this->board->eraseBoard($i);
			}
			return;
		}
		$this->setIncrementRound(true);
	}

	private function waitBoardLoad($sleepTime = 2000000)
	{
		usleep($sleepTime);
	}

	private function setResetGuess($bool)
	{
		$this->resetGuess = $bool;
	}

	private function setIncrementRound($bool)
	{
		$this->incrementRound = $bool;
	}

	private function incrementRound()
	{
		if ($this->incrementRound) {
			$this->round++;
			$this->readGuessPosition++;
		}
	}

	public function checkWin()
	{
		return $this->board->checkWinGame();
	}

	public function finishHim($ultimate)
	{
		$this->finish();
		$this->board->open(false);
		$this->board->unlockBoard();
		$this->board->makeGuess($ultimate);
	}
}
