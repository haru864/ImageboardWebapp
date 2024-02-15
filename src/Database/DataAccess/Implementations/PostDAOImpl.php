<?php

namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\PostDAO;
use Database\DatabaseManager;
use Models\Post;
use Exceptions\InvalidDataException;

class PostDAOImpl implements PostDAO
{
    public function create(Post $postData): bool
    {
        if (
            $postData->getPostId() !== null
            || $postData->getContent() !== null
            || $postData->getContent() !== null
        ) {
            throw new InvalidDataException('Cannot create a post with specified parameter.');
        }
        if ($postData->getContent() === null) {
            throw new InvalidDataException('Cannot create a post with null.');
        }
        return $this->createOrUpdate($postData);
    }

    public function getById(int $id): ?Post
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        $postRecord = $mysqli->prepareAndFetchAll("SELECT * FROM post WHERE post_id = ?", 'i', [$id])[0] ?? null;
        return $postRecord === null ? null : $this->convertRecordToPost($postRecord);
    }

    public function update(Post $postData): bool
    {
        if ($postData->getPostId() === null) {
            throw new InvalidDataException('Post specified has no ID.');
        }
        $postRecord = $this->getById($postData->getPostId());
        if ($postRecord === null) {
            throw new InvalidDataException(sprintf("Post '%s' does not exist.", $postData->getPostId()));
        }
        return $this->createOrUpdate($postData);
    }

    public function delete(int $id): bool
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        return $mysqli->prepareAndExecute("DELETE FROM post WHERE id = ?", 'i', [$id]);
    }

    public function createOrUpdate(Post $postData): bool
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        $query = <<<SQL
            INSERT INTO post (
                post_id, reply_to_id, subject, content, created_at, updated_at, image_path, thumbnail_path
            )
            VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?
            )
            ON DUPLICATE KEY UPDATE
                updated_at = ?
            ;
        SQL;
        $result = $mysqli->prepareAndExecute(
            $query,
            'iisssssss',
            [
                $postData->getPostId(),
                $postData->getReplyToId(),
                $postData->getSubject(),
                $postData->getContent(),
                $postData->getCreatedAt(),
                $postData->getUpdatedAt(),
                $postData->getImagePath(),
                $postData->getThumbnailPath(),
                $postData->getUpdatedAt()
            ],
        );
        if (!$result) {
            return false;
        }
        return true;
    }

    public function getAllThreads(int $offset, int $limit): array
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        $query = <<<SQL
            SELECT * FROM post WHERE reply_to_id IS NULL LIMIT ? OFFSET ?;
        SQL;
        $postRecords = $mysqli->prepareAndFetchAll($query, 'ii', [$offset, $limit]);
        return $postRecords;
    }

    public function getReplies(Post $postData, int $offset, int $limit): array
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        $query = <<<SQL
            SELECT * FROM post WHERE reply_to_id = ? LIMIT ? OFFSET ?;
        SQL;
        $postRecords = $mysqli->prepareAndFetchAll($query, 'iii', [$postData->getPostId(), $offset, $limit]);
        return $postRecords;
    }

    private function convertRecordToPost(array $data): Post
    {
        return new Post(
            postId: $data['post_id'],
            replyToId: $data['reply_to_id'],
            subject: $data['subject'],
            content: $data['content'],
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'],
            imagePath: $data['image_path'],
            thumbnailPath: $data['thumbnail_path']
        );
    }
}
