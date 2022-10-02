<?php

use Facebook\Webdriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\{WebDriverBy, WebDriverExpectedCondition, WebDriverKeys};

class BoardTerm {
	private $host;
	private $options;
	private $browser;
	private $driver;
	private $word;

	function __construct(Word $word) {
		$this->word = $word;

		$this->host = 'http://localhost:4444/wd/hub';
	}

	public function open()
	{
		$this->options = new ChromeOptions();
		$this->options->addArguments(["--incognito"]);

		$this->browser = Facebook\WebDriver\Remote\DesiredCapabilities::chrome($this->options);
		$this->browser->setCapability('chromeOptions', $this->options);

		$this->driver = Facebook\WebDriver\Remote\RemoteWebDriver::create(
			$this->host, 
			$this->browser,
		);
		
		$this->driver->get("https://term.ooo/");
		$this->driver->manage()->window();
	}

	public function close()
	{
		$this->driver->quit();
	}
	
	public function unlockBoard()
	{
		// document.querySelector("body > wc-modal").shadowRoot.querySelector("#box")
		$this->driver->findElement(WebDriverBy::xpath('/html/body/wc-modal//div'))->click();
		// $this->driver->findElement(WebDriverBy::id('box'))->click();
	}
	
	public function pressEnterKey()
	{
		$this->driver->findElement(WebDriverBy::xpath('kbd_enter'))->click();
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
			$modal = $this->driver->findElement(WebDriverBy::cssSelector("#stats"));
			return false;
		} catch (\Throwable $th) {
			return true;
		}
		//throw $th;
		// $modalStatus = $modal->getAttribute('class');
		
		// return $modalStatus !== 'show';
	}
	
	public function checkWordIsRightPlace($round, $cel)
	{
		$cel = $this->driver->findElement(
			WebDriverBy::cssSelector("#board > div:nth-child($round) > div:nth-child($cel)")
		);
		$celClass = $cel->getAttribute('class');

		return $celClass == 'letter right';
	}
	
	public function checkWordIsMissPlaced($round, $cel)
	{
		$cel = $this->driver->findElement(
			WebDriverBy::cssSelector("#board > div:nth-child($round) > div:nth-child($cel)")
		);
		$celClass = $cel->getAttribute('class');

		return $celClass == 'letter place';
	}
	
	public function checkWordIsWrong($round, $cel)
	{
		$cel = $this->driver->findElement(
			WebDriverBy::cssSelector("#board > div:nth-child($round) > div:nth-child($cel)")
		);
		$celClass = $cel->getAttribute('class');

		return $celClass == 'letter wrong';
	}

	public function getWord($round, $cel)
	{
		$word = $this->word->clean(
			$this->driver->findElement(
				WebDriverBy::cssSelector("#board > div:nth-child($round) > div:nth-child($cel)")
			)->getText()
		);
		
		return $word;
	}

	public function hasAlertOnBoard()
	{
		$alert = $this->driver->findElement(WebDriverBy::cssSelector("#msg"));
		$alertIsOpen = $alert->getAttribute('open');
		$alertText = $alert->getText();
		
		return $alertIsOpen && $alertText == 'essa palavra não é aceita';
	}

	public function checkWinGame()
	{
		$alert = $this->driver->findElement(WebDriverBy::cssSelector("#msg"));
		$alertIsOpen = $alert->getAttribute('open');
		$alertText = $alert->getText();
		
		return $alertIsOpen && strpos($alertText, 'palavra certa') === false;
	}

	public function eraseBoard($cel)
	{
		$this->driver->findElement(
			WebDriverBy::cssSelector("#board > div:nth-child($cel)")
		)->sendKeys(WebDriverKeys::BACKSPACE);
	}
}