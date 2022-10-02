<?php

use Facebook\Webdriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\{WebDriverBy, WebDriverExpectedCondition, WebDriverKeys};

// java -jar selenium-server-standalone-3.141.59.jar
set_error_handler('exceptions_error_handler');

function custom_autoloader($class) {
    include 'core/' . $class . '.php';
}

spl_autoload_register('custom_autoloader');

// ini_set('default_charset','UTF-8');

require_once(__DIR__ . '/vendor/autoload.php');

    $host = 'http://localhost:4444/wd/hub';

    $options = new ChromeOptions();
    $options->addArguments(["--incognito"]);

    $browser = Facebook\WebDriver\Remote\DesiredCapabilities::chrome($options);
    $browser->setCapability('chromeOptions', $options);

    $driver = Facebook\WebDriver\Remote\RemoteWebDriver::create(
        $host, 
        $browser,
    );

    $driver->get("https://term.ooo/");

    $driver->manage()->window();
    // $driver->manage()->window()->maximize();

    $driver->findElement(WebDriverBy::id('modal'))->click();

    $enter_key = $driver->findElement(WebDriverBy::id('kbd_enter'));

    $cont = 1;
    $chutes = [
        // '1' => ['a', 'n', 'i', 'm', 'e'],
        // '2' => ['f', 'o', 'c', 'u', 's'],
        '1' => ['r', 'o', 's', 'e', 'a'],
        '2' => ['m', 'u', 'n', 'd', 'i'],
        // '1' => ['o', 'v', 'a', 'd', 'a'],
        // '4' => ['e', 'g', 'i', 'd', 'e'],
        // '4' => ['p', 'i', 'a', 'd', 'a'],
        // '5' => ['p', 'i', 'a', 'b', 'a'],
        // '6' => ['b', 'a', 'i', 'x', 'o'],
    ];
    //3 => caiba
    $chutes_feitos = [];

    $rights = [];
    $missfits = [];
    $missfits_used = [];
    $wrongs = [];

    uniqueBadWords();

    while ($cont < 7) {
        foreach ($chutes[$cont] as $letra) {
            // chutes
            // echo "chutes...  " . $letra . PHP_EOL;
            $driver->findElement(WebDriverBy::cssSelector("#board > div:nth-child($cont)"))->sendKeys($letra);
        }
        $enter_key->click();
        usleep(1000000);
        $chutes_feitos[] = implode('', $chutes[$cont]);

        // verificar se palavra é aceita, caso não seja salvar ela em arquivo e apagar da tela
        $alert = $driver->findElement(WebDriverBy::cssSelector("#msg"));
        $alert_is_open = $alert->getAttribute('open');
        $alert_text = $alert->getText();
        $reset_guess = false;
        if ($alert_is_open && $alert_text == 'essa palavra não é aceita') {
            $fp = fopen('wrong.txt', 'a');
            fwrite($fp, implode('', $chutes[$cont])."-");
            fclose($fp);
            foreach ($chutes[$cont] as $letra) {
                $driver->findElement(WebDriverBy::cssSelector("#board > div:nth-child($cont)"))->sendKeys(WebDriverKeys::BACKSPACE);
            }
            $reset_guess = true;
        }
        usleep(1000000);

        
        // verificar acertos perfeitos e parciais
        $i = 1;
        foreach ($chutes[$cont] as $letra) {
            // echo "#board > div:nth-child($cont) > div:nth-child($i)" . PHP_EOL;
            // echo $i . PHP_EOL;
            // print_r($chutes[$cont]);
            // echo "letra...." . $letra . PHP_EOL;
            // $i = $i > 5 ? 5 : $i;
            $cel = $driver->findElement(WebDriverBy::cssSelector("#board > div:nth-child($cont) > div:nth-child($i)"));
            $class_name = $cel->getAttribute('class');
            
            if ($class_name == 'letter place') {
                if ((!in_array($letra, $rights)) !== false && !array_key_exists($letra, $missfits)) { # || !array_key_exists($i, $rights)
                    $missfits[$letra][] = $i;
                }
            }

            if ($class_name == 'letter right') {
                $rights[$i] = $letra;
                $cont_rights = array_count_values($rights);
                if (in_array($letra, $rights)) {
                    if (
                        (array_key_exists($letra, $missfits)) !== false 
                        // && 
                        // substr_count(implode('', $chutes[$cont]), $letra) != $cont_rights[$letra]
                    ) {
                        // echo 'removendo...', $letra,PHP_EOL;
                        unset($missfits[$letra]);
                    }
                    if ((array_key_exists($letra, $missfits_used)) !== false) {
                        unset($missfits_used[$letra]);
                    }
                }
            }

            if ($class_name == 'letter wrong') {
                $wrongs[] = $letra;
            }

            $i++;
        }
        
        usleep(1000000);

        if ($reset_guess) $next_guess = createGuessRight($rights);
        // echo "---------------------" . PHP_EOL;
        // print_r($rights);
        // print_r($missfits);
        // print_r($missfits_used);
        // echo $next_guess . PHP_EOL;
        // echo "---------------------" . PHP_EOL;


        if (!$alert_is_open) {
            $cont++;
        }
        // // buscar e adicionar nova palavra correta
        if ($alert_is_open || $cont > 2) {
            $complete_guess = false;
            $test_guess = 0;
            while($complete_guess == false) {
                $next_guess = createGuess($missfits, $next_guess, $rights, $reset_guess, $missfits_used);
                $complete_guess = getWord($next_guess, $chutes_feitos, $wrongs);
                $test_guess++;
                if ($test_guess == 10) {
                    $reset_guess = true;
                }
            }
            $chutes[$cont] = str_split($complete_guess);
            $alert_is_open = NULL;
        }

    }
    echo 'fim';

    function cleanWord($word, $length = 1) {
        $word = str_replace("/", "", $word);
        $tmp = preg_split('~~u', $word, -1, PREG_SPLIT_NO_EMPTY);
        if ($length > 1) {
            $chunks = array_chunk($tmp, $length);
            foreach ($chunks as $i => $chunk) {
                $chunks[$i] = join('', (array) $chunk);
            }
            $tmp = $chunks;
        }

        $special_words = [
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
        $word = implode('', $tmp);
        foreach (array_unique($tmp) as $char){
            if (array_key_exists($char, $special_words)) {
                $word = str_replace($char, $special_words[$char], $word);
            }
        }

        return $word;
    }

    function uniqueBadWords() {
        // limpar arquivo de palavras ruims, deixando sem repeticoes
        $wrong_words = array_filter(array_unique(explode("-", file_get_contents('wrong.txt'))));

        $fp = fopen('wrong.txt', 'w');
        fwrite($fp, implode('-', $wrong_words) . "-");
        fclose($fp);
    }

    function createGuess($missfits, $next_guess, $rights, $reset_guess, &$missfits_used) {
        // echo "---------------------" . PHP_EOL;
        // print_r($rights);
        // print_r($missfits);
        // print_r($missfits_used);
        // echo $next_guess . PHP_EOL;
        // echo "---------------------" . PHP_EOL;
        // die();
        $next_guess = $reset_guess ? '-----' : $next_guess;
        // criar chute alternando as letras de amarelo, sem repetir posicao já usada
        if ($reset_guess) {
            $next_guess = createGuessRight($rights);
    
            // echo $next_guess;
            $possible_open_position = [
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                '5' => 5
            ];
            $round_buzy = [];
            $missfits_keys = array_keys($missfits);
            ksort($missfits_keys);
            foreach ($missfits_keys as $letter) {
                foreach ($missfits[$letter] as $key => $position) {
                    // print_r($missfits);
                    // print_r($missfits[$letter]);
                    // echo $position . PHP_EOL;
                    // echo $letter . PHP_EOL;
                    $check_key = false;
                    if (array_key_exists($position, $rights) && $rights[$position] == $letter) {
                        $check_key = true;
                    }
                    $right_positions = array_keys($rights);

                    $used_positions = [];
                    $lastPos = 0;
                    while (($lastPos = strpos($next_guess, '-', $lastPos))!== false) {
                        $used_positions[] = $lastPos + 1;
                        $lastPos = $lastPos + strlen('-');
                    }
            
                    // $find_position = true;
                    if (!array_key_exists($letter, $missfits_used)) {
                        $missfits_used[$letter] = [];
                    }
                    if (!$check_key && strpos($next_guess, $letter) !== $position) {
                        $right_with_replace = array_merge($right_positions, [$position], $missfits_used[$letter], $round_buzy);
                        $right_with_replace = array_filter(array_unique($right_with_replace));
                        $position_to_place = array_keys(array_diff($possible_open_position, $right_with_replace));
                        // echo "---------POSSIBLE $letter ------------" . PHP_EOL;
                        // print_r($right_positions);
                        // print_r([$position]);
                        // print_r($missfits_used[$letter]);
                        // print_r($round_buzy);
                        // print_r($right_with_replace);
                        // echo "--------FIM POSSIBLE-----------" . PHP_EOL;
                        // echo "--------OPEN-----------" . PHP_EOL;
                        // print_r($position_to_place);
                        // echo "--------FIM OPEN-----------" . PHP_EOL;
                        $good_random = true;
                        while ($good_random) {
                            $used_positions = [];
                            $lastPos = 0;
                            $by_pass = false;
                            while (($lastPos = strpos($next_guess, '-', $lastPos))!== false) {
                                $used_positions[] = $lastPos + 1;
                                $lastPos = $lastPos + strlen('-');
                            }
                            
                            if (count($position_to_place) && count(array_intersect($position_to_place, $used_positions))) {
                                $rand_position = array_rand($position_to_place, 1);
                            } else {
                                $by_pass = true;
                                $right_with_replace = array_merge($right_positions, [$position], $used_positions);
                                $right_with_replace = array_filter(array_unique($right_with_replace));
                                $position_to_place = array_keys($right_with_replace);
                                $rand_position = array_rand($position_to_place, 1);
                            }
                            $guess_position = $position_to_place[$rand_position];
                            if (in_array($guess_position, $used_positions)) {
                                $good_random = false;
                            }
                            if ($by_pass) {
                                $key = array_search($rand_position, $used_positions);
                                if ($key) {
                                    $guess_position = $used_positions[$key];
                                } else if (array_key_exists($rand_position, $used_positions)) {

                                    $guess_position = $used_positions[$rand_position];
                                }
                            }

                            if (in_array($guess_position, $used_positions) && $by_pass) {
                                $good_random = false;
                            }
                        }
                        // do {
                            
                        //     echo "ABBBBBBA    " . PHP_EOL;
                        //     echo $next_guess . PHP_EOL;
                        //     var_dump($used_positions);
                        //     var_dump($position_to_place);
                        //     echo "cccccccccccc    ",$rand_position . PHP_EOL;
                        // $guess_position = $position_to_place[$rand_position];

                        //     echo "AAAAAA    ",$guess_position . PHP_EOL;
                        //     $check = str_split($next_guess);
                        //     if ($guess_position == 0) {
                        //         $guess_position = 0;
                        //     } else {
                        //         $guess_position--;
                        //     }
                        //     var_dump($check);
                        // } while($check[$guess_position] !== '-');
                        // echo "--------POSS-----------" . PHP_EOL;
                        // echo $rand_position . PHP_EOL;
                        // echo $guess_position . PHP_EOL;
                        // echo $letter . PHP_EOL;
                        // echo "--------FIM POSS-----------" . PHP_EOL;
                        $round_buzy[] = $guess_position;
                        $next_guess = substr_replace($next_guess, $letter, $guess_position - 1, 1);
                        $missfits_used[$letter][] = $guess_position;
                        // echo "--------MISS-----------" . PHP_EOL;
                        // print_r($missfits_used);
                        // echo "--------FIM MISS-----------" . PHP_EOL;
                    }
                }
            }
        }
        
        // print_r($round_buzy);
        // die();
        return $next_guess;
    }



    function createGuessRight($rights) {
        // criar posicao com as letras corretas
        $next_guess = '-----';
        ksort($rights);
        foreach ($rights as $position => $letter) {
            $next_guess = substr_replace($next_guess, $letter, $position - 1, 1);
        }

        return $next_guess;
    }

    function getWord($guess, $chutes_feitos, $wrongs) {
        // echo "ainda buscando... $guess" . PHP_EOL;
        // $guess = 'eri-m';
        echo "ainda buscando... $guess" . PHP_EOL;
        // echo PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL .$guess . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL ;
        $url = "https://www.dicionarioinformal.com.br/caca-palavras/5-letras/$guess";
        echo "url..." .$url . PHP_EOL ;

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url);
        $html_content = utf8_encode( $response->getBody()->getContents());

        $special_words = [
            'a' => '[aàáâãäå]',
            'e' => '[eèéêë]',
            'i' => '[iìíîï]',
            'o' => '[oòóôõö]',
            'u' => '[uùúûü]',
        ];

        foreach (array_unique(str_split($guess)) as $char){
            if (array_key_exists($char, $special_words)) {
                $guess = str_replace($char, $special_words[$char], $guess);
            }
        }
        
        
        $alphabet = implode('', range('a','z'));
        $alphabet = str_replace($wrongs, "", $alphabet);
        $regex_string = str_replace("-", "[".$alphabet."éáűőúöüóí]", $guess);
        $re = '/([\/]'. $regex_string .')/mu';
        preg_match_all($re, $html_content, $matches, PREG_SET_ORDER, 0);

        // echo $re.PHP_EOL;
        // var_dump($html_content);
        // var_dump($matches);
        // die();
        
        // pegar melhor palavra possivel
        $wrong_words = explode("-", file_get_contents('wrong.txt'));
        if (sizeof($matches) == 0 ) {
            return false;
        }
        $not_good_enough = true;
        $word_get = 0;
        $word = cleanWord($matches[$word_get][1]);
        // echo $word . PHP_EOL;
        // echo 'TRANSLIT : ', iconv("UTF-8", "ISO-8859-1//TRANSLIT", $word), PHP_EOL;
        // echo 'IGNORE   : ', iconv("UTF-8", "ISO-8859-1//IGNORE", $word), PHP_EOL;
        // echo 'Plain    : ', iconv("UTF-8", "ISO-8859-1", $word), PHP_EOL;
        // echo $word . PHP_EOL;
        // die();
        $find_word = true;
        $bad_list = array_merge($chutes_feitos, $wrong_words);
        
        while ($find_word) {
            if (in_array($word, $bad_list)) {
                $word_get++;
                try {
                    $word = cleanWord($matches[$word_get][1]);
                } catch (\ErrorException $e) {
                    $word = false;
                    break;
                }
            } else {
                $find_word = false;
            }
        }
        return $word;
    }

function exceptions_error_handler($severity, $message, $filename, $lineno) {
    if (error_reporting() == 0) {
        return;
    }
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $filename, $lineno);
    }
}
