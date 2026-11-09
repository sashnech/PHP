<?php

class Forum
{
    private $topics = [];

    public function addTopic(Topic $topic)
    {
        $this->topics[] = $topic;
    }

    public function listByAuthor($author)
    {
        return array_values(array_filter(
            $this->topics,
            function ($topic) use ($author) {
                return $topic->getAuthor() === $author;
            }
        ));
    }

    public function listPinned()
    {
        return array_values(array_filter(
            $this->topics,
            function ($topic) {
                return $topic instanceof PinnedTopic;
            }
        ));
    }
}
