-- Lisää CREATE TABLE lauseet tähän tiedostoon
CREATE TABLE Chef (
id SERIAL PRIMARY KEY,
name TEXT UNIQUE NOT NULL,
password TEXT NOT NULL
);

CREATE TABLE Recipe (
id SERIAL PRIMARY KEY,
chef_id INTEGER REFERENCES Chef(id),
name TEXT NOT NULL,
cooking_time TEXT,
directions TEXT,
published DATE
);

CREATE TABLE Comment ( 
chef_id INTEGER REFERENCES Chef(id),
recipe_id INTEGER REFERENCES Recipe(id),
rating INTEGER NOT NULL,
comment TEXT NOT NULL
);

CREATE TABLE Ingredient (
id SERIAL PRIMARY KEY,
name TEXT
);

CREATE TABLE Keyword (
id SERIAL PRIMARY KEY,
keyword TEXT
);

CREATE TABLE RecipeIngredient (
recipe_id INTEGER REFERENCES Recipe(id),
ingredient_id INTEGER REFERENCES Ingredient(id),
quantity TEXT
);

CREATE TABLE RecipeKeyword (
recipe_id INTEGER REFERENCES Recipe(id),
keyword_id INTEGER REFERENCES Keyword(id)
);