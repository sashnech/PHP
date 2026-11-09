<?php

class PinnedTopic extends Topic
{
    private $pinnedUntil;

    public function __construct($title, $author, $createdAt, $pinnedUntil)
    {
        parent::__construct($title, $author, $createdAt);
        $this->pinnedUntil = $pinnedUntil;
    }

    public function getInfo()
    {
        return parent::getInfo()
            . ', закріплено до: '
            . date('d.m.Y H:i', strtotime($this->pinnedUntil));
    }
}
