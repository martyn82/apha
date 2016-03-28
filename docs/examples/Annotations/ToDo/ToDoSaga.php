<?php
declare(strict_types = 1);

namespace Apha\Examples\Annotations\ToDo;

use Apha\Annotations\Annotation\EndSaga;
use Apha\Annotations\Annotation\SagaEventHandler;
use Apha\Annotations\Annotation\StartSaga;
use Apha\Domain\Identity;
use Apha\Examples\Domain\ToDo\DeadlineExpired;
use Apha\Examples\Domain\ToDo\ToDoItemCreated;
use Apha\Examples\Domain\ToDo\ToDoItemDone;
use Apha\Saga\Annotation\AnnotatedSaga;
use Apha\Scheduling\EventScheduler;
use Apha\Scheduling\ScheduleToken;
use JMS\Serializer\Annotation as Serializer;
use Psr\Log\LoggerInterface;

class ToDoSaga extends AnnotatedSaga
{
    /**
     * @Serializer\Type("Apha\Scheduling\ScheduleToken")
     * @var ScheduleToken
     */
    private $expireToken;

    /**
     * @Serializer\Exclude()
     * @var EventScheduler
     */
    private $scheduler;

    /**
     * @Serializer\Exclude()
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        parent::__construct($identity);
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param EventScheduler $scheduler
     */
    public function setEventScheduler(EventScheduler $scheduler)
    {
        $this->scheduler = $scheduler;
    }

    /**
     * @StartSaga()
     * @SagaEventHandler(associationProperty = "identity")
     * @param ToDoItemCreated $event
     */
    public function onToDoItemCreated(ToDoItemCreated $event)
    {
        $token = $this->scheduler->scheduleAfter(
            new \DateInterval("PT{$event->getExpireSeconds()}S"),
            new DeadlineExpired($event->getIdentity())
        );

        $this->expireToken = $token;
    }

    /**
     * @EndSaga()
     * @SagaEventHandler(associationProperty = "identity")
     * @param ToDoItemDone $event
     */
    public function onToDoItemDone(ToDoItemDone $event)
    {
        $this->scheduler->cancelSchedule($this->expireToken);
        $this->expireToken = null;
    }

    /**
     * @EndSaga()
     * @SagaEventHandler(associationProperty = "identity")
     * @param DeadlineExpired $event
     */
    public function onDeadlineExpired(DeadlineExpired $event)
    {
        $this->expireToken = null;
    }

    /**
     */
    protected function start()
    {
        parent::start();
        $this->logger->debug("SAGA STARTED", [
            'sagaIdentity' => $this->getId()->getValue()
        ]);
    }

    /**
     */
    protected function end()
    {
        parent::end();
        $this->logger->debug("SAGA STOPPED", [
            'sagaIdentity' => $this->getId()->getValue()
        ]);
    }
}