<?php

class WordSearch {
	private $word;

	function __construct(Word $word) 
	{
		$this->word = $word;
	}

	public function find($wordToUrl, $wrongWords, $guessWordDone, $wrongs)
	{
		$wordToUrl = strtolower($wordToUrl);
		$url = "https://www.dicionarioinformal.com.br/caca-palavras/5-letras/$wordToUrl";
        echo "url..." .$url . PHP_EOL ;

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url);
        $htmlContent = utf8_encode( $response->getBody()->getContents());

		$re = $this->makeRegex($wordToUrl, $wrongs);
		
		preg_match_all($re, $htmlContent, $matches, PREG_SET_ORDER, 0);
		
        if (sizeof($matches) == 0 ) {
            return false;
		}
		
        $wordGet = 0;
		$word = strtolower($this->word->clean($matches[$wordGet][1]));
        $badList = array_merge($guessWordDone, $wrongWords);

        $findWord = true;
        while ($findWord) {
            if (in_array($word, $badList)) {
                $wordGet++;
                try {
                    $word = strtolower($this->word->clean($matches[$wordGet][1]));
                } catch (\ErrorException $e) {
                    $word = false;
                    break;
                }
            } else {
                $findWord = false;
            }
        }
        return $word;
	}

	private function makeRegex($word, $wrongs)
	{
		$special_words = [
            'a' => '[aàáâãäå]',
            'e' => '[eèéêë]',
            'i' => '[iìíîï]',
            'o' => '[oòóôõö]',
            'u' => '[uùúûü]',
        ];

        foreach (array_unique(str_split($word)) as $char){
            if (array_key_exists($char, $special_words)) {
                $word = str_replace($char, $special_words[$char], $word);
            }
        }
        
        
        $alphabet = implode('', range('a','z'));
        $alphabet = str_replace($wrongs, "", $alphabet);
        $regex_string = str_replace("-", "[".$alphabet."éáűőúöüóí]", $word);
		
		return '/([\/]'. $regex_string .')/mu';
	}
}