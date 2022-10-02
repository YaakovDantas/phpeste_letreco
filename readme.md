# TERM & LETRECO BOT

## Configuration:

1° Clone this repository

`git clone !!`

2° Install dependecies

`composer install`

3° Install Java if you don't already have it:

`sudo apt-get install openjdk-8-jre -y`

4° Download the latest [Selenium](https://selenium-release.storage.googleapis.com/index.html?path=3.5/) standalone server and Run:

###### Run this command in a second terminal tab, and let it running.

`java -jar selenium-server-standalone-3.5.3.jar`

5° Get the latest [Chrome](https://chromedriver.chromium.org/downloads) driver

6° After extract the chrome driver on 5° step, move to /bin

`sudo mv -i chromedriver /usr/bin/`


## Options


| Option Nam  | Description                      | Default      | Options        |
| ----------- | -----------                      | -----------  | -----------    |
| --game      | name of the game                 | letreco      | term - letreco |
| --trygard   | keep guessing until win the game | off          | on - off       |


## Examples (`php index.php --game=term|letreco --tryhard=on|off`)


##### It will run letreco and play until the victory
`php index.php --game=letreco --tryhard=on`

##### It will run letreco and will only make 6 possible guesses
`php index.php --game=letreco --tryhard=off`

##### It will run term and play until the victory
`php index.php --game=term --tryhard=on`

##### It will run term and will only make 6 possible guesses
`php index.php --game=term --tryhard=off`

##### It will run term and will only make 6 possible guesses
`php index.php --game=term`

##### It will run letreco and will only make 6 possible guesses
`php index.php --game=letreco`

##### It will run letreco and will only make 6 possible guesses
`php index.php --tryhard=off`

##### It will run letreco and play until the victory
`php index.php --tryhard=on`

##### It will run letreco and will only make 6 possible guesses
`php index.php`


## Notes

As the game goes on, it will make a file called 'wrong.txt'.
It will make a list of all words that it is not acceptble on the game
