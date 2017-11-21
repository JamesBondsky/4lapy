<?php

namespace LinguaLeo\ExpertSender;

use GuzzleHttp\Client;
use LinguaLeo\ExpertSender\Entities\Column;
use LinguaLeo\ExpertSender\Entities\Property;
use LinguaLeo\ExpertSender\Entities\Receiver;
use LinguaLeo\ExpertSender\Entities\Snippet;
use LinguaLeo\ExpertSender\Entities\Where;
use LinguaLeo\ExpertSender\Request\AddUserToList;
use PHPUnit\Framework\TestCase;

class ExpertSenderTest extends TestCase
{
    /** @var ExpertSender */
    protected $expertSender;

    /** @var Request\AddUserToList */
    protected $addUserToListRequest;

    /** @var array|null */
    protected $params;

    public function setUp()
    {
        parent::setUp();

        $this->expertSender = new ExpertSender(
            $this->getParam('url'),
            $this->getParam('key'),
            new Client()
        );

        // minimal required request setup
        $this->addUserToListRequest = (new Request\AddUserToList())
            ->setListId($this->getParam('testList'))
            ->setEmail('example@example.com');
    }

    public function getParams()
    {
        $paramsPath = __DIR__.'/params.json';

        if (!is_file($paramsPath)) {
            $this->markTestSkipped('params.json is required to run this test');
        }

        return json_decode(file_get_contents($paramsPath), 1);
    }

    public function getParam($param)
    {
        if (!$this->params) {
            $this->params = $this->getParams();
            if (!$this->params) {
                $this->params = [];
            }
        }

        if (!isset($this->params[$param])) {
            $this->markTestSkipped($param.' must be configured in params.json to run this test');
        }

        return $this->params[$param];
    }

    public function getTestListId()
    {
        return $this->getParam('testList');
    }

    public function getTestTrigger()
    {
        return $this->getParam('testTrigger');
    }

    public function getTestTransactional()
    {
        return $this->getParam('testTransactional');
    }

    public function getTestEmailPattern()
    {
        return $this->getParam('testGmailEmailPattern');
    }

    public function getTestTableName()
    {
        return $this->getParam('testTableName');
    }

    public function testLists()
    {
        $randomEmail = sprintf('some_random_%s@gmail.com', mt_rand(0, 100000000000).mt_rand(0, 1000000000000));

        $trackingCode = 'phpunit'.time();

        $request = (new Request\AddUserToList())
            ->setEmail($randomEmail)
            ->setListId($this->getTestListId())
            ->addProperty(new Property(1775, ExpertSenderEnum::TYPE_STRING, 'female'))
            ->setFirstName('Alex')
            ->setLastName('Lastname')
            ->setName('Alex Lastname')
            ->setVendor('phpunit tests')
            ->setTrackingCode($trackingCode)
            ->setForce(false);

        $addResult = $this->expertSender->addUserToList($request);

        $this->assertTrue($addResult->isOk());
        $this->assertEquals(0, $addResult->getErrorCode());
        $this->assertEquals('', $addResult->getErrorMessage());

        $deleteResult = $this->expertSender->deleteUser($randomEmail);
        $this->assertTrue($deleteResult->isOk());

        $invalidDeleteResult = $this->expertSender->deleteUser($randomEmail);
        $this->assertFalse($invalidDeleteResult->isOk());
        $this->assertEquals(404, $invalidDeleteResult->getErrorCode());
        $this->assertRegExp('~not found~', $invalidDeleteResult->getErrorMessage());
    }

    /**
     * @group table
     */
    public function testAddTableRow()
    {
        $result = $this->expertSender->addTableRow($this->getTestTableName(), [
            new Column('name', 'Alex'),
            new Column('age', 22),
            new Column('sex', 1),
            new Column('created_at', date(DATE_W3C)),
        ]);
        $this->assertTrue($result->isOk());
    }

    /**
     * @group   table
     * @depends testAddTableRow
     */
    public function testGetTableData()
    {
        $result = $this->expertSender->getTableData(
            $this->getTestTableName(),
            [new Column('name'), new Column('sex')],
            [new Where('age', ExpertSenderEnum::OPERATOR_EQUALS, 22)]
        );
        $this->assertTrue($result->isOk());
        $tableData = $result->getData();
        $this->assertCount(2, $tableData);
        $this->assertEquals(['Alex', 'True'], $tableData[1]);
    }

    /**
     * @group   table
     * @depends testAddTableRow
     */
    public function testUpdateTableRow()
    {
        $result = $this->expertSender->updateTableRow($this->getTestTableName(), [
            new Column('name', 'Alex'),
            new Column('age', 22),
        ], [
            new Column('sex', 0),
            new Column('created_at', date(DATE_W3C, strtotime('-1 week'))),
        ]);
        $this->assertTrue($result->isOk());
    }

    /**
     * @group   table
     * @depends testUpdateTableRow
     */
    public function testDeleteTableRow()
    {
        $result = $this->expertSender->deleteTableRow($this->getTestTableName(), [
            new Column('name', 'Alex'),
            new Column('age', 22),
        ]);
        $this->assertTrue($result->isOk());
    }

    public function testChangeEmail()
    {
        $randomEmail = sprintf('some_random_%s@gmail.com', mt_rand(0, 100000000000).mt_rand(0, 1000000000000));
        $randomEmail2 = sprintf('some_random_%s@gmail.com', mt_rand(0, 100000000000).mt_rand(0, 1000000000000));

        $listId = $this->getTestListId();

        $addUserToList = (new AddUserToList())
            ->setEmail($randomEmail)
            ->setListId($listId)
            ->setProperties([new Property(1775, ExpertSenderEnum::TYPE_STRING, 'female')])
            ->setName('Alex');
        $this->expertSender->addUserToList($addUserToList);

        $result = $this->expertSender->getUserId($randomEmail);
        $oldId = $result->getId();
        $this->assertTrue(is_numeric($oldId));

        $this->expertSender->changeEmail($listId, $randomEmail, $randomEmail2);
        $result = $this->expertSender->getUserId($randomEmail2);
        $this->assertEquals($result->getId(), $oldId);

        try {
            $this->expertSender->getUserId($randomEmail);
            $exceptionThrown = false;
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    }

    public function testSendTrigger()
    {
        $randomEmail = sprintf($this->getTestEmailPattern(), mt_rand(0, 100000000000).mt_rand(0, 1000000000000));
        $listId = $this->getTestListId();

        $addUserToList = (new AddUserToList())
            ->setEmail($randomEmail)
            ->setListId($listId)
            ->setProperties([new Property(1775, ExpertSenderEnum::TYPE_STRING, 'male')])
            ->setName('Vladimir');
        $this->expertSender->addUserToList($addUserToList);

        $this->expertSender->sendTrigger($this->getTestTrigger(), [new Receiver($randomEmail)]);

        $this->assertTrue(true);
    }

    public function testSendTransactional()
    {
        $randomEmail = sprintf($this->getTestEmailPattern(), mt_rand(0, 100000000000).mt_rand(0, 1000000000000));
        $listId = $this->getTestListId();

        $addUserToList = (new AddUserToList())
            ->setEmail($randomEmail)
            ->setListId($listId)
            ->setProperties([new Property(1775, ExpertSenderEnum::TYPE_STRING, 'male')])
            ->setName('Vladimir');
        $this->expertSender->addUserToList($addUserToList);

        $this->expertSender->sendTransactional(
            $this->getTestTransactional(),
            new Receiver($randomEmail),
            [new Snippet('code', 123456)]
        );

        $this->assertTrue(true);
    }

    public function testAddUserToListAcceptsAndFreezesRequest()
    {
        $result = $this->expertSender->addUserToList($this->addUserToListRequest);

        $this->assertTrue($this->addUserToListRequest->isFrozen());

        $this->assertTrue($result->isOk());
    }
}
