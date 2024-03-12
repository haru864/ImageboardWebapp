<?php

namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\PostDAO;
use Database\DatabaseManager;
use Models\Post;
use Exceptions\InvalidDataException;
use Exceptions\QueryFailedException;

class PostDAOImpl implements PostDAO
{
    public function create(Post $post): int
    {
        if ($post->getPostId() !== null) {
            throw new InvalidDataException('Cannot create a post with id.');
        }
        if ($post->getContent() === null) {
            throw new InvalidDataException('Cannot create a post with null.');
        }
        $mysqli = DatabaseManager::getMysqliConnection();
        $query = <<<SQL
            INSERT INTO post (
                reply_to_id, subject, content, created_at, updated_at, image_file_name, image_file_extension
            )
            VALUES (
                ?, ?, ?, ?, ?, ?, ?
            )
        SQL;
        $result = $mysqli->prepareAndExecute(
            $query,
            'issssss',
            [
                $post->getReplyToId(),
                $post->getSubject(),
                $post->getContent(),
                $post->getCreatedAt(),
                $post->getUpdatedAt(),
                $post->getImageFileName(),
                $post->getImageFileExtension()
            ],
        );
        if (!$result) {
            throw new QueryFailedException('INSERT failed.');
        }
        return $mysqli->insert_id;
    }

    public function getById(int $id): ?Post
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        $postRecord = $mysqli->prepareAndFetchAll("SELECT * FROM post WHERE post_id = ?", 'i', [$id])[0] ?? null;
        return $postRecord === null ? null : $this->convertRecordToPost($postRecord);
    }

    public function update(Post $post): bool
    {
        if ($post->getPostId() === null) {
            throw new InvalidDataException('Post specified has no ID.');
        }
        $postRecord = $this->getById($post->getPostId());
        if ($postRecord === null) {
            throw new InvalidDataException(sprintf("Post-ID '%s' does not exist.", $post->getPostId()));
        }
        $mysqli = DatabaseManager::getMysqliConnection();
        $query = <<<SQL
            UPDATE
                post
            SET
                reply_to_id = ?,
                subject = ?,
                content = ?,
                created_at = ?,
                updated_at = ?,
                image_file_name = ?,
                image_file_extension = ?
            WHERE
                post_id = ?
        SQL;
        $result = $mysqli->prepareAndExecute(
            $query,
            'issssssi',
            [
                $post->getReplyToId(),
                $post->getSubject(),
                $post->getContent(),
                $post->getCreatedAt(),
                $post->getUpdatedAt(),
                $post->getImageFileName(),
                $post->getImageFileExtension(),
                $post->getPostId()
            ],
        );
        if (!$result) {
            throw new QueryFailedException('UPDATE failed.');
        }
        return $mysqli->insert_id;
    }

    public function delete(int $id): bool
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        $sql = "DELETE FROM post WHERE post_id = ?";
        return $mysqli->prepareAndExecute($sql, 'i', [$id]);
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

    public function getInactiveThreadIds(int $inactivePeriodHours): array
    {
        $dateTime = new \DateTime();
        $interval = \DateInterval::createFromDateString("- {$inactivePeriodHours} hours");
        $dateTime->add($interval);
        $mysqli = DatabaseManager::getMysqliConnection();
        $sql = <<<SQL
            SELECT
                post_id
            FROM
                post
            WHERE
                reply_to_id IS NULL
                AND
                updated_at < ?
        SQL;
        return $mysqli->prepareAndFetchAll($sql, 's', [$dateTime->format('Y-m-d H:i:s')]);
    }

    private function convertRecordArrayToPostArray(array $records): array
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
            imageFileName: $data['image_file_name'],
            imageFileExtension: $data['image_file_extension']
        );
    }
}
