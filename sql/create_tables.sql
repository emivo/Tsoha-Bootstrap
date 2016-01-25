-- Lisää CREATE TABLE lauseet tähän tiedostoon
CREATE TABLE Chef (
id SERIAL PRIMARY KEY,
name varchar(50) UNIQUE NOT NULL,
pswrd varchar(50) NOT NULL
);