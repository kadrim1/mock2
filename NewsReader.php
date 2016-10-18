<?php
//http://manuel.kiessling.net/2014/05/08/mocking-dependencies-in-php-unit-tests-with-mockery/

class NewsReader
{ //db-st uudiste pealkirjade väljastamise funktsioon
    public function getAllHeadlinesUppercase(\Doctrine\DBAL\Connection $dbConnection)
    {
        $headlines = array();

        $statement = $dbConnection->executeQuery('SELECT headline FROM news ORDER BY id DESC');

        while ($row = $statement->fetch()) {
            $headlines[] = strtoupper($row['headline']);
        }

        return $headlines;
    }
}