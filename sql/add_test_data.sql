INSERT INTO Chef (name, admin, active, password) VALUES ('admin', TRUE, TRUE, '$1$sillysal$YI2tx55Nw4QYX8JYh2qt90');
INSERT INTO Chef (name, admin, active, password) VALUES ('kallekoekokki', FALSE, TRUE, '$1$sillysal$YI2tx55Nw4QYX8JYh2qt90');

INSERT INTO Recipe (name, chef_id, cooking_time, directions, published) 
VALUES ('Köyhät Ritarit', (SELECT id FROM Chef WHERE name LIKE 'admin'),'15 min', 'Vatkaa munan rakenne rikki. Lisää maito ja sokeri. Kasta pullat munamaitoon. Ruskista pannulla rasvassa. Tarjoile hillon ja kermavaahdon kera.', NOW());

INSERT INTO Recipe (name, chef_id, cooking_time, directions, published)
VALUES ('Perunamuussi', (SELECT id FROM Chef WHERE name LIKE 'kallekoekokki'),'30 min', 'Keitä perunat ja muussaa. Sekoita muut aineet perunoihin', NOW());


INSERT INTO Ingredient (name) VALUES ('pullaviipale');
INSERT INTO Ingredient (name) VALUES ('vanilijasokeri');
INSERT INTO Ingredient (name) VALUES ('muna');
INSERT INTO Ingredient (name) VALUES ('maitoa');

INSERT INTO Ingredient (name) VALUES ('perunaa');
INSERT INTO Ingredient (name) VALUES ('suolaa');
INSERT INTO Ingredient (name) VALUES ('voita');

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

INSERT INTO Keyword (keyword) VALUES ('jälkiruoka');
INSERT INTO Keyword (keyword) VALUES ('lisäke');

INSERT INTO RecipeKeyword (recipe_id, keyword_id)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Köyhät Ritarit'), (SELECT id FROM Keyword WHERE keyword LIKE 'jälkiruoka'));

INSERT INTO RecipeKeyword (recipe_id, keyword_id)
VALUES ((SELECT id FROM Recipe WHERE name LIKE 'Perunamuussi'), (SELECT id FROM Keyword WHERE keyword LIKE 'lisäke'));

INSERT INTO Comment (chef_id, recipe_id, rating, comment) VALUES ((SELECT id FROM Chef WHERE name LIKE 'admin'), (SELECT id FROM Recipe WHERE name LIKE 'Köyhät Ritarit'), 4, 'ei maistu salami');

