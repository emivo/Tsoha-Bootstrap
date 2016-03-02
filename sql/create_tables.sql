-- Lisää CREATE TABLE lauseet tähän tiedostoon
CREATE TABLE Chef (
id SERIAL PRIMARY KEY,
admin BOOLEAN,
active BOOLEAN,
name TEXT UNIQUE NOT NULL,
info TEXT,
password TEXT NOT NULL
);

CREATE TABLE Recipe (
id SERIAL PRIMARY KEY,
chef_id INTEGER REFERENCES Chef(id) ON DELETE CASCADE,
name TEXT NOT NULL,
cooking_time TEXT,
directions TEXT,
published DATE
);

CREATE TABLE Comment ( 
chef_id INTEGER REFERENCES Chef(id) ON DELETE CASCADE,
recipe_id INTEGER REFERENCES Recipe(id) ON DELETE CASCADE,
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
recipe_id INTEGER REFERENCES Recipe(id) ON DELETE CASCADE,
ingredient_id INTEGER REFERENCES Ingredient(id) ON DELETE CASCADE,
quantity TEXT
);

CREATE TABLE RecipeKeyword (
recipe_id INTEGER REFERENCES Recipe(id) ON DELETE CASCADE,
keyword_id INTEGER REFERENCES Keyword(id) ON DELETE CASCADE
);