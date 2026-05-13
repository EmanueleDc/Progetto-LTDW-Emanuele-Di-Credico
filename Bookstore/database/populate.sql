SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM order_items;
DELETE FROM orders;
DELETE FROM cart_items;
DELETE FROM users_has_groups;
DELETE FROM users;
DELETE FROM groups;
DELETE FROM books_has_categories;
DELETE FROM categories;
DELETE FROM books_has_authors;
DELETE FROM authors;
DELETE FROM books;
SET FOREIGN_KEY_CHECKS = 1;


INSERT INTO groups (name, description) VALUES 
('admin', 'Amministratori del sistema'),
('user', 'Utenti standard');


INSERT INTO users (username, email, password_hash) VALUES 
('admin', 'admin@bookstories.com', '$2y$10$8v5p.C8X1Gf3V7I0/k.E.eeDk/8J8aU3.N.m/qY1oKk/Y0f/rO.kO'); 


INSERT INTO users_has_groups (user_id, group_id) 
SELECT u.id, g.id 
FROM users u, groups g 
WHERE u.username = 'admin' AND g.name = 'admin';


INSERT INTO categories (name, description) VALUES 
('Narrativa', 'Libri di narrativa e romanzi'),
('Saggistica', 'Testi informativi e saggi'),
('Scienza', 'Libri scientifici e tecnici'),
('Storia', 'Libri di storia e biografie');


INSERT INTO authors (name, biography) VALUES 
('Dante Alighieri', 'Poeta, scrittore e politico italiano.'),
('Alessandro Manzoni', 'Scrittore e poeta italiano.'),
('Isaac Asimov', 'Biochimico e scrittore di fantascienza.'),
('Stephen King', 'Scrittore statunitense di horror e fantasy.');

INSERT INTO books (title, isbn, price, stock, description, cover_image) VALUES 
('La Divina Commedia', '9780123456789', 19.90, 50, 'Il capolavoro di Dante Alighieri.', '1778664566_book cover 1.png'),
('I Promessi Sposi', '9780123456780', 15.50, 30, 'Il celebre romanzo di Alessandro Manzoni.', '1778593902_book cover2.jpg'),
('Fondazione', '9780123456781', 12.00, 100, 'Il primo libro del ciclo della Fondazione di Asimov.', '1778664577_book cover 3.png'),
('It', '9780123456782', 18.00, 45, 'Uno dei capolavori horror di Stephen King.', '1778664588_book cover 4.png'),
('Io, Robot', '9780123456783', 10.50, 20, 'Raccolta di racconti di Isaac Asimov.', '1778664597_book cover 5.png');


INSERT INTO books_has_authors (book_id, author_id) 
SELECT b.id, a.id FROM books b, authors a WHERE b.title = 'La Divina Commedia' AND a.name = 'Dante Alighieri';
INSERT INTO books_has_authors (book_id, author_id) 
SELECT b.id, a.id FROM books b, authors a WHERE b.title = 'I Promessi Sposi' AND a.name = 'Alessandro Manzoni';
INSERT INTO books_has_authors (book_id, author_id) 
SELECT b.id, a.id FROM books b, authors a WHERE b.title = 'Fondazione' AND a.name = 'Isaac Asimov';
INSERT INTO books_has_authors (book_id, author_id) 
SELECT b.id, a.id FROM books b, authors a WHERE b.title = 'It' AND a.name = 'Stephen King';
INSERT INTO books_has_authors (book_id, author_id) 
SELECT b.id, a.id FROM books b, authors a WHERE b.title = 'Io, Robot' AND a.name = 'Isaac Asimov';


INSERT INTO books_has_categories (book_id, category_id) 
SELECT b.id, c.id FROM books b, categories c WHERE b.title = 'La Divina Commedia' AND c.name = 'Narrativa';
INSERT INTO books_has_categories (book_id, category_id) 
SELECT b.id, c.id FROM books b, categories c WHERE b.title = 'I Promessi Sposi' AND c.name = 'Narrativa';
INSERT INTO books_has_categories (book_id, category_id) 
SELECT b.id, c.id FROM books b, categories c WHERE b.title = 'Fondazione' AND c.name = 'Scienza';
INSERT INTO books_has_categories (book_id, category_id) 
SELECT b.id, c.id FROM books b, categories c WHERE b.title = 'It' AND c.name = 'Narrativa';
INSERT INTO books_has_categories (book_id, category_id) 
SELECT b.id, c.id FROM books b, categories c WHERE b.title = 'Io, Robot' AND c.name = 'Scienza';


INSERT INTO services (username, description) VALUES 
('admin_access', 'Accesso al pannello di amministrazione'),
('manage_books', 'Possibilità di aggiungere/modificare libri'),
('manage_orders', 'Possibilità di gestire gli ordini');

INSERT INTO services_has_groups (service_username, group_id)
SELECT s.username, g.id 
FROM services s, groups g 
WHERE g.name = 'admin';

