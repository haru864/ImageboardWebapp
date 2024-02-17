<?php

namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\PostDAO;
use Database\DatabaseManager;
use Models\Post;
use Exceptions\InvalidDataException;
use Exceptions\QueryFailedException;

class PostDAOImpl implements PostDAO
{
    public function create(Post $postData): int
    {
        if ($postData->getPostId() !== null) {
            throw new InvalidDataException('Cannot create a post with id.');
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

    public function createOrUpdate(Post $postData): int
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        $query = <<<SQL
            INSERT INTO post (
                reply_to_id, subject, content, created_at, updated_at, image_path, thumbnail_path
            )
            VALUES (
                ?, ?, ?, ?, ?, ?, ?
            )
            ON DUPLICATE KEY UPDATE
                updated_at = ?
            ;
        SQL;
        $result = $mysqli->prepareAndExecute(
            $query,
            'isssssss',
            [
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
            throw new QueryFailedException('UPSERT post failed.');
        }
        return $mysqli->insert_id;
    }

    public function getAllThreads(?int $offset = null, ?int $limit = null): array
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        if (is_null($offset) || is_null($limit)) {
            $query = "SELECT * FROM post WHERE reply_to_id IS NULL";
            $postRecords = $mysqli->prepareAndFetchAll($query, '', []);
        } else {
            $query = "SELECT * FROM post WHERE reply_to_id IS NULL LIMIT ? OFFSET ?";
            $postRecords = $mysqli->prepareAndFetchAll($query, 'ii', [$offset, $limit]);
        }
        return $this->convertRecordArrayToPostArray($postRecords);
    }

    public function getReplies(Post $postData, ?int $offset = null, ?int $limit = null): array
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        if (is_null($offset) || is_null($limit)) {
            $query = "SELECT * FROM post WHERE reply_to_id = ?";
            $postRecords = $mysqli->prepareAndFetchAll($query, 'i', [$postData->getPostId()]);
        } else {
            $query = "SELECT * FROM post WHERE reply_to_id = ? LIMIT ? OFFSET ?";
            $postRecords = $mysqli->prepareAndFetchAll($query, 'iii', [$postData->getPostId(), $limit, $offset]);
        }
        return $this->convertRecordArrayToPostArray($postRecords);
    }

    private function convertRecordArrayToPostArray(array $records)
    {
        $posts = [];
        foreach ($records as $record) {
            $post = $this->convertRecordToPost($record);
            array_push($posts, $post);
        }
        return $posts;
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
