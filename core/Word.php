<?php

class Word {

	function __construct() 
	{
		$this->gamePositions = [
			'1' => 1,
			'2' => 2,
			'3' => 3,
			'4' => 4,
			'5' => 5
		];
		
		$this->specialWords = [
			'À' => 'A',
			'Á' => 'A',
			'Â' => 'A',
			'Ã' => 'A',
			'Ä' => 'A',
			'Å' => 'A',
			'È' => 'E',
			'É' => 'E',
			'Ê' => 'E',
			'Ë' => 'E',
			'Ì' => 'I',
			'Í' => 'I',
			'Î' => 'I',
			'Ï' => 'I',
			'Ò' => 'O',
			'Ó' => 'O',
			'Ô' => 'O',
			'Õ' => 'O',
			'Ö' => 'O',
			'Ù' => 'U',
			'Ú' => 'U',
			'Û' => 'U',
			'Ü' => 'U',
			'à' => 'a',
			'á' => 'a',
			'â' => 'a',
			'ã' => 'a',
			'ä' => 'a',
			'å' => 'a',
			'è' => 'e',
			'é' => 'e',
			'ê' => 'e',
			'ë' => 'e',
			'ì' => 'i',
			'í' => 'i',
			'î' => 'i',
			'ï' => 'i',
			'ò' => 'o',
			'ó' => 'o',
			'ô' => 'o',
			'õ' => 'o',
			'ö' => 'o',
			'ù' => 'u',
			'ú' => 'u',
			'û' => 'u',
			'ü' => 'u',
		];
	}

	private function createGuessWithRightWords($rights) {
        // criar posicao com as letras corretas
        $nextGuess = '-----';
		ksort($rights);
        foreach ($rights as $position => $letter) {
            $nextGuess = substr_replace($nextGuess, $letter, $position - 1, 1);
        }

        return $nextGuess;
	}

	private function getUsedPositions($nextGuess)
	{
		$usedPositions = [];
		$lastPos = 0;
		while (($lastPos = strpos($nextGuess, '-', $lastPos)) !== false) {
			$usedPositions[] = $lastPos + 1;
			$lastPos = $lastPos + strlen('-');
		}
		return $usedPositions;
	}
	
	private function createMissplaceUsed($letter, $missplaceUsed)
	{
		if (!array_key_exists($letter, $missplaceUsed)) {
			$missplaceUsed[$letter] = [];
		}
		return $missplaceUsed;
	}

	private function canUseLetter($key, $rights, $letter)
	{
		$hasKeyOnRight = false;
		if (array_key_exists($key, $rights) && $rights[$key] == $letter) {
			$hasKeyOnRight = true;
		}

		return $hasKeyOnRight;
	}
	
	private function canUsePosition($nextGuess, $letter, $position)
	{
		return strpos($nextGuess, $letter) !== $position;
	}

	private function getBusyPositions(...$arrList)
	{
		$mergedArr = [];
		foreach ($arrList as $arr) {
			$mergedArr = array_merge($arr, $mergedArr);
		}
		$busyPositionsList = array_filter(array_unique($mergedArr));
		return $busyPositionsList;
	}
	
	private function getRightWordsKeys($rightWords)
	{
		return array_keys($rightWords);
	}
	
	private function getFreePositions($possiblePositions)
	{
		return array_keys(array_diff($this->gamePositions, $possiblePositions));
	}
	
	private function getMissfitplacedKeys($missPlacedWords)
	{
		$missfitsKeys = array_keys($missPlacedWords);
		shuffle($missfitsKeys);
		return $missfitsKeys;
	}
	
	private function getGoodPositionToSetMissplace($nextGuess, $freePositions, $rightWordsKeys, $position)
	{
		$goodRandom = true;
		while ($goodRandom) {
			$byPass = false;
			$usedPositions = $this->getUsedPositions($nextGuess);
			
			if (count($freePositions) && count(array_intersect($freePositions, $usedPositions))) {
				$randPosition = array_rand($freePositions, 1);
			} else {
				$byPass = true;
				$possiblePositions = $this->getBusyPositions($rightWordsKeys, $position, $usedPositions);
				$freePositions = array_keys($possiblePositions);
				$randPosition = array_rand($freePositions, 1);
			}

			$guessPosition = $freePositions[$randPosition];
			if (in_array($guessPosition, $usedPositions)) {
				$goodRandom = false;
			}

			if ($byPass) {
				$key = array_search($randPosition, $usedPositions);
				if ($key) {
					$guessPosition = $usedPositions[$key];
				} else if (array_key_exists($randPosition, $usedPositions)) {

					$guessPosition = $usedPositions[$randPosition];
				}
			}

			if (in_array($guessPosition, $usedPositions) && $byPass) {
				$goodRandom = false;
			}
		}
		return $guessPosition;
	}

	private function insertNextGuess($nextGuess, $goodPositionToPlace, $letter)
	{
		return substr_replace($nextGuess, $letter, $goodPositionToPlace - 1, 1);
	}

	public function makeNextGuess(
		$rightWords,
		$missPlacedWords,
		$missplaceUsed,
		$gameObject
	) {
		
		$nextGuess = $this->createGuessWithRightWords($rightWords);
		$roundTakenPosition = [];

		$missfitsKeys = $this->getMissfitplacedKeys($missPlacedWords);
		
		foreach ($missfitsKeys as $letter) {
			foreach ($missPlacedWords[$letter] as $key => $position) {
				// echo "letter > $letter | key > $key -> position > $position" . PHP_EOL;

				$usedPositions = $this->getUsedPositions($nextGuess);
				$missplaceUsed = $this->createMissplaceUsed($letter, $missplaceUsed);

				$isFreeLetter = $this->canUseLetter($position, $rightWords, $letter);
				$isFreePosition = $this->canUsePosition($nextGuess, $letter, $position);

				$rightWordsKeys = $this->getRightWordsKeys($rightWords);

				if (!$isFreeLetter && $isFreePosition) {
					$possiblePositions = $this->getBusyPositions($rightWordsKeys, [$position], $missplaceUsed[$letter], $roundTakenPosition);
					$freePositions = $this->getFreePositions($possiblePositions);

					$goodPositionToPlace = $this->getGoodPositionToSetMissplace($nextGuess, $freePositions, $rightWordsKeys, [$position]);
					$nextGuess = $this->insertNextGuess($nextGuess, $goodPositionToPlace, $letter);

					$roundTakenPosition[] = $goodPositionToPlace;
					$gameObject->setMissplaceUsed($letter, $goodPositionToPlace);
				}
			}
		}

		return $nextGuess;
	}

	public function clean($word, $length = 1) {
		$word = strtoupper($word);
		$word = str_replace("/", "", $word);
		$tmp = preg_split('~~u', $word, -1, PREG_SPLIT_NO_EMPTY);
		if ($length > 1) {
			$chunks = array_chunk($tmp, $length);
			foreach ($chunks as $i => $chunk) {
				$chunks[$i] = join('', (array) $chunk);
			}
			$tmp = $chunks;
		}

		
		$word = implode('', $tmp);
		foreach (array_unique($tmp) as $char){
			if (array_key_exists($char, $this->specialWords)) {
				$word = str_replace($char, $this->specialWords[$char], $word);
			}
		}
		
		return strtoupper($word);
    }
}