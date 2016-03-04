INSERT INTO Chef (name, admin, active, info, password) VALUES ('admin', TRUE, TRUE, 'Olen sivuston ylläpitäjä. Voit ottaa yhteyttä minuun sähköpostitse email (at) email . org', '$1$sillysal$YI2tx55Nw4QYX8JYh2qt90');
INSERT INTO Chef (name, admin, active, info, password) VALUES ('kallekoekokki', FALSE, TRUE, 'Olen tyypillinen kotikokki ja teen itseäni miellyttäviä reseptejä', '$1$sillysal$YI2tx55Nw4QYX8JYh2qt90');

INSERT INTO Recipe (name, chef_id, cooking_time, directions, published) 
VALUES ('Köyhät Ritarit', (SELECT id FROM Chef WHERE name LIKE 'admin'),'15 min', 'Vatkaa munan rakenne rikki. Lisää maito ja sokeri. Kasta pullat munamaitoon. Ruskista pannulla rasvassa. Tarjoile hillon ja kermavaahdon kera.', NOW());

INSERT INTO Recipe (name, chef_id, cooking_time, directions, published)
VALUES ('Perunamuussi', (SELECT id FROM Chef WHERE name LIKE 'kallekoekokki'),'30 min', 'Keitä perunat ja muussaa. Sekoita muut aineet perunoihin', NOW());
INSERT INTO Recipe (name, chef_id, cooking_time, directions, published)
VALUES ('Mantelikala', (SELECT id FROM Chef WHERE name LIKE 'kallekoekokki'),'35-40 min', 'Lämmitä uuni 225°C:een. Laita jäinen kala uunin keskiosaan, kunnes kala on kypsää ja väriltään kullanruskea.', NOW());
INSERT INTO Recipe (name, chef_id, cooking_time, directions, published)
VALUES ('Pizzaleivät', (SELECT id FROM Chef WHERE name LIKE 'kallekoekokki'),'15 min', 'Laita ainekset leivälle ja lämmitä uunissa tai mikrossa kunnes juusto sulaa', NOW());


INSERT INTO Ingredient (name) VALUES ('pullaviipale');
INSERT INTO Ingredient (name) VALUES ('vanilijasokeri');
INSERT INTO Ingredient (name) VALUES ('muna');
INSERT INTO Ingredient (name) VALUES ('maitoa');

INSERT INTO Ingredient (name) VALUES ('perunaa');
INSERT INTO Ingredient (name) VALUES ('suolaa');
INSERT INTO Ingredient (name) VALUES ('voita');

INSERT INTO Ingredient (name) VALUES ('Pakaste mantelikala');

INSERT INTO Ingredient (name) VALUES ('Leipää');
INSERT INTO Ingredient (name) VALUES ('Juustoa');
INSERT INTO Ingredient (name) VALUES ('Ketsuppia');
INSERT INTO Ingredient (name) VALUES ('Meetvurstia');

INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Köyhät Ritarit'), (SELECT id FROM Ingredient WHERE name LIKE 'pullaviipale'), '10');

INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Köyhät Ritarit'), (SELECT id FROM Ingredient WHERE name LIKE 'vanilijasokeri'), '1 tl');

INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Köyhät Ritarit'), (SELECT id FROM Ingredient WHERE name LIKE 'muna'), '1');

INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Köyhät Ritarit'), (SELECT id FROM Ingredient WHERE name LIKE 'maitoa'), '2 dl');

INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Perunamuussi'), (SELECT id FROM Ingredient WHERE name LIKE 'maitoa'), '2 dl');

INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Perunamuussi'), (SELECT id FROM Ingredient WHERE name LIKE 'perunaa'), '5');

INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Perunamuussi'), (SELECT id FROM Ingredient WHERE name LIKE 'suolaa'), 'ripaus');
INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Perunamuussi'), (SELECT id FROM Ingredient WHERE name LIKE 'voita'), '1 rkl');

INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Mantelikala'), (SELECT id FROM Ingredient WHERE name LIKE 'Pakaste mantelikala'), '1');

INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Pizzaleivät'), (SELECT id FROM Ingredient WHERE name LIKE 'Leipää'), '2');
INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Pizzaleivät'), (SELECT id FROM Ingredient WHERE name LIKE 'Juustoa'), 'reilusti');
INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Pizzaleivät'), (SELECT id FROM Ingredient WHERE name LIKE 'Ketsuppia'), '1 rkl');
INSERT INTO RecipeIngredient (recipe_id, ingredient_id, quantity)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Pizzaleivät'), (SELECT id FROM Ingredient WHERE name LIKE 'Meetvurstia'), '4');

INSERT INTO Keyword (keyword) VALUES ('Jälkiruoka');
INSERT INTO Keyword (keyword) VALUES ('Pääruoka');
INSERT INTO Keyword (keyword) VALUES ('Välipala');
INSERT INTO Keyword (keyword) VALUES ('Muu');
INSERT INTO Keyword (keyword) VALUES ('Alkuruoka');
INSERT INTO Keyword (keyword) VALUES ('Lisäke');

INSERT INTO RecipeKeyword (recipe_id, keyword_id)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Köyhät Ritarit'), (SELECT id FROM Keyword WHERE keyword LIKE 'Jälkiruoka'));

INSERT INTO RecipeKeyword (recipe_id, keyword_id)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Perunamuussi'), (SELECT id FROM Keyword WHERE keyword LIKE 'Lisäke'));

INSERT INTO RecipeKeyword (recipe_id, keyword_id)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Perunamuussi'), (SELECT id FROM Keyword WHERE keyword LIKE 'Muu'));

INSERT INTO RecipeKeyword (recipe_id, keyword_id)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Mantelikala'), (SELECT id FROM Keyword WHERE keyword LIKE 'Pääruoka'));

INSERT INTO RecipeKeyword (recipe_id, keyword_id)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Pizzaleivät'), (SELECT id FROM Keyword WHERE keyword LIKE 'Välipala'));

INSERT INTO Comment (chef_id, recipe_id, rating, comment) VALUES ((SELECT id FROM Chef WHERE name LIKE 'admin'), (SELECT id FROM Recipe WHERE name LIKE 'Köyhät Ritarit'), 4, 'ei maistu salami');

INSERT INTO Comment (chef_id, recipe_id, rating, comment) VALUES ((SELECT id FROM Chef WHERE name LIKE 'kallekoekokki'), (SELECT id FROM Recipe WHERE name LIKE 'Köyhät Ritarit'), 3, 'en ole makean ystävä');

INSERT INTO Comment (chef_id, recipe_id, rating, comment) VALUES ((SELECT id FROM Chef WHERE name LIKE 'kallekoekokki'), (SELECT id FROM Recipe WHERE name LIKE 'Perunamuussi'), 5, 'Paras ikinä');

