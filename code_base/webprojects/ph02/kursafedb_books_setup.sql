USE books;

DROP TABLE IF EXISTS books;

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    topic VARCHAR(255) NOT NULL,
    address_optional VARCHAR(255) NULL,
    rating DECIMAL(2,1) NOT NULL,
    published_date VARCHAR(100) NOT NULL,
    book_type VARCHAR(255) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    resource_url_optional VARCHAR(2048) NULL
);

INSERT INTO books (title, author, topic, address_optional, rating, published_date, book_type, image_url, resource_url_optional)
VALUES
    ('Clean Code', 'Robert C. Martin', 'Software Engineering', 'Prentice Hall', 4.6, '2008', 'Programming', 'uploads/image.png', 'https://www.lkhibra.ma/books/clean-code.pdf'),
    ('The Pragmatic Programmer', 'Andrew Hunt and David Thomas', 'Software Craft', 'Addison-Wesley', 4.7, '1999', 'Programming', NULL, NULL)
