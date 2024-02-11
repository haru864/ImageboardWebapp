<?php

namespace Models;

use Models\Interfaces\Model;
use Models\Traits\GenericModel;

class PostDTO implements Model
{
    use GenericModel;

    public int $postId;
    public ?int $replyToId;
    public ?string $subject;
    public string $content;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(int $postId, ?int $replyToId = null, ?string $subject = null, string $content, string $createdAt, string $updatedAt)
    {
        $this->postId = $postId;
        $this->replyToId = $replyToId;
        $this->subject = $subject;
        $this->content = $content;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
}
