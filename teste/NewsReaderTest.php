<?php
require_once 'C:\xampp\htdocs\mock2\NewsReader.php';

//tahame testida funktsiooni getAllHeadlinesUppercasei, kas see muudab ja tagastab tulemuse nagu peab
//aga me ei taha teha päris databasei connectionit
class NewsReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAllHeadlinesUppercase()

    {
        //kutsume välja mocki objekti, millesse sisestama klassi nime, mida tahame
        $mockedDbConnection = \Mockery::mock('\Doctrine\DBAL\Connection');
        //
        $mockedStatement = \Mockery::mock('\Doctrine\DBAL\Driver\Statement');

        //kuidas peaks mockitud connection käituma, kui executeQuery väljakutsutakse
        $mockedDbConnection
            ->shouldReceive('executeQuery')
            ->with('SELECT headline FROM news ORDER BY id DESC')
            ->andReturn($mockedStatement);
        //ootab, et rows tagastatakse, seega peame ka selle looma
        $mockedRows = array(
            array('headline' => 'First headline'),
            array('headline' => 'Second headline')
        );

        $mockedStatement
            ->shouldReceive('fetch')
            ->andReturnUsing(function () use (&$mockedRows) {
                $row = current($mockedRows);
                next($mockedRows);
                return $row;
            });
        //sisestame mockitud väärtused õigesse kohta
        $newsReader = new NewsReader();

        $expectedHeadlines = array('FIRST HEADLINE', 'SECOND HEADLINE');
        $actualHeadlines = $newsReader->getAllHeadlinesUppercase($mockedDbConnection);

        $this->assertEquals($expectedHeadlines, $actualHeadlines);

        //1. simuleeritud andmebaas 2. kontrollitud, et getAllHeadlinesUppercase töötab 3. andmebaasipäringud on õigesti koostatud
    }
}