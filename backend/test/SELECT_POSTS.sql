SELECT post_id, reply_to_id, LEFT(subject, 10) AS subject, LEFT(content, 10) AS content, created_at, updated_at, image_file_name, image_file_extension FROM post;
