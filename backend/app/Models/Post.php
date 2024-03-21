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
    public ?string $content;
    public string $createdAt;
    public string $updatedAt;
    public ?string $imageFileName;
    public ?string $imageFileExtension;

    public function __construct(
        ?int $postId,
        ?int $replyToId = null,
        ?string $subject = null,
        ?string $content,
        string $createdAt,
        string $updatedAt,
        ?string $imageFileName,
        ?string $imageFileExtension
    ) {
        $this->postId = $postId;
        $this->replyToId = $replyToId;
        $this->subject = $subject;
        $this->content = $content;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->imageFileName = $imageFileName;
        $this->imageFileExtension = $imageFileExtension;
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

    public function getContent(): ?string
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

    public function getImageFileName(): ?string
    {
        return $this->imageFileName;
    }

    public function setImageFileName(string $imageFileName): void
    {
        $this->imageFileName = $imageFileName;
    }

    public function getImageFileExtension(): ?string
    {
        return $this->imageFileExtension;
    }

    public function setImageFileExtension(string $imageFileExtension): void
    {
        $this->imageFileExtension = $imageFileExtension;
    }
}
