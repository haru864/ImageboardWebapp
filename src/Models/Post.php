<?php

namespace Models;

use Models\Interfaces\Model;
use Models\Traits\GenericModel;

class Post implements Model
{
    use GenericModel;

    public ?int $postId;
    public ?int $replyToId;
    public ?string $subject;
    public string $content;
    public string $createdAt;
    public string $updatedAt;
    public string $imagePath;
    public string $thumbnailPath;

    public function __construct(
        ?int $postId,
        ?int $replyToId = null,
        ?string $subject = null,
        string $content,
        string $createdAt,
        string $updatedAt,
        string $imagePath,
        string $thumbnailPath
    ) {
        $this->postId = $postId;
        $this->replyToId = $replyToId;
        $this->subject = $subject;
        $this->content = $content;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->imagePath = $imagePath;
        $this->thumbnailPath = $thumbnailPath;
    }

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function setPostId(int $id): void
    {
        $this->postId = $id;
    }

    public function getReplyToId(): ?int
    {
        return $this->replyToId;
    }

    public function setReplyToId(int $replyToId): void
    {
        $this->replyToId = $replyToId;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): void
    {
        $this->imagePath = $imagePath;
    }

    public function getThumbnailPath(): string
    {
        return $this->thumbnailPath;
    }

    public function setThumbnailPath(string $thumbnailPath): void
    {
        $this->thumbnailPath = $thumbnailPath;
    }
}
