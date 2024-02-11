<?php

namespace Database\DataAccess\Interfaces;

use Models\PostDTO;

interface PostDAO
{
    public function create(PostDTO $postData): bool;
    public function getById(int $id): ?PostDTO;
    public function update(PostDTO $postData): bool;
    public function delete(int $id): bool;
    public function createOrUpdate(PostDTO $postData): bool;

    /**
     * @param int $offset
     * @param int $limit
     * @return PostDTO[] メインスレッドであるすべての投稿、つまり他の投稿への返信でない投稿、つまりreplyToIDがnullである投稿
     */
    public function getAllThreads(int $offset, int $limit): array;

    /**
     * @param PostDTO $postData - すべての返信が属する投稿
     * @param int $offset
     * @param int $limit
     * @return PostDTO[] 本スレッドへの返信であるすべての投稿、つまりreplyToID = $postData->getId()となります。
     */
    public function getReplies(PostDTO $postData, int $offset, int $limit): array;
}
