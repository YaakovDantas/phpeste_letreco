<?php

use Facebook\Webdriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\{WebDriverBy, WebDriverExpectedCondition, WebDriverKeys};

class BoardLetreco {
	private $host;
	private $options;
	private $browser;
	private $driver;
	private $word;

	function __construct(Word $word) {
		$this->word = $word;

		$this->host = 'http://localhost:4444/wd/hub';
	}

	public function open($incognito = true)
	{
		$this->options = new ChromeOptions();
		if ($incognito) {
			$this->options->addArguments(["--incognito"]);
		}

		$this->browser = Facebook\WebDriver\Remote\DesiredCapabilities::chrome($this->options);
		$this->browser->setCapability('chromeOptions', $this->options);

		$this->driver = Facebook\WebDriver\Remote\RemoteWebDriver::create(
			$this->host, 
			$this->browser,
		);
		
		$this->driver->get("https://www.gabtoschi.com/letreco/");
		$this->driver->manage()->window();
	}

	public function close()
	{
		$this->driver->quit();
	}
	
	public function unlockBoard()
	{
		// 
	}
	
	public function pressEnterKey()
	{
		$this->driver->findElement(WebDriverBy::xpath('/html/body/div/div/div[2]/div[2]/div[1]/button[2]'))->click();
	}
	
	public function makeGuess($word)
	{
		$word = str_split($word);
		foreach ($word as $char) {
			$this->driver->findElement(
				WebDriverBy::tagName("body")
			)->sendKeys($char);
		}
		$this->pressEnterKey();
	}

	public function checkGameOver()
	{
		try {
			$modal = $this->driver->findElement(WebDriverBy::cssSelector("#root > div > div:nth-child(2) > div.overlay-screen"));
			return false;
		} catch (\Throwable $th) {
			return true;
		}
	}
	
	public function checkWordIsRightPlace($round, $cel)
	{
		$cel = $this->driver->findElement(
			WebDriverBy::xpath("//*[@id='root']/div/div[2]/div[1]/div/div/div[$round]/div[$cel]")
		);
		$celClass = $cel->getAttribute('class');

		return strpos($celClass, 'right');
	}
	
	public function checkWordIsMissPlaced($round, $cel)
	{
		$cel = $this->driver->findElement(
			WebDriverBy::xpath("//*[@id='root']/div/div[2]/div[1]/div/div/div[$round]/div[$cel]")
		);
		$celClass = $cel->getAttribute('class');

		return strpos($celClass, 'displaced');
	}
	
	public function checkWordIsWrong($round, $cel)
	{
		$cel = $this->driver->findElement(
			WebDriverBy::xpath("//*[@id='root']/div/div[2]/div[1]/div/div/div[$round]/div[$cel]")
		);
		$celClass = $cel->getAttribute('class');

		return strpos($celClass, 'wrong');
	}

	public function getWord($round, $cel)
	{
		$word = $this->word->clean(
			$this->driver->findElement(
				WebDriverBy::xpath("//*[@id='root']/div/div[2]/div[1]/div/div/div[$round]/div[$cel]")
			)->getText()
		);
		
		return $word;
	}

	public function hasAlertOnBoard()
	{
		try {
			$alert = $this->driver->findElement(WebDriverBy::xpath('//*[@id="root"]/div/div[2]/div[2]/div[2]/button[1]'));
			$alertIsOpen = $alert->getAttribute('disabled');
			
			return $alertIsOpen == "true";
		} catch (\Throwable $th) {
			return false;
		}
		
	}

	public function checkWinGame()
	{
		$alert = $this->driver->findElement(WebDriverBy::xpath('//*[@id="root"]/div/div[2]/div[1]/div/div/div/div[1]/div/h1'));
		$alertText = $alert->getText();

		return $alertText == 'Você acertou!';
	}

	public function eraseBoard($cel)
	{
		$this->driver->findElement(
			WebDriverBy::xpath('//*[@id="root"]/div/div[2]/div[2]/div[1]/button[1]')
		)->click();
	}
}