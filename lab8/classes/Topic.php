<?php

class Topic
{
    protected $title;
    private $author;
    protected $createdAt;

    public function __construct($title, $author, $createdAt)
    {
        $this->title = $title;
        $this->author = $author;
        $this->createdAt = $createdAt;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getInfo()
    {
        return $this->title
            . ' - автор: ' . $this->author
            . ', створено: ' . date('d.m.Y H:i', strtotime($this->createdAt));
    }
}
