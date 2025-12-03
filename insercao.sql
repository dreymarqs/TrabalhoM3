INSERT INTO Cliente (Nome, Email) VALUES
('Lucas Almeida', 'lucas@gmail.com'),
('Mariana Souza', 'mariana@hotmail.com'),
('Pedro Henrique', 'pedrohenrique@yahoo.com');

INSERT INTO Cliente_telefone (id_Cliente, Numero, Tipo) VALUES
(1, '47999887766', 'Celular'),
(1, '4733445566', 'Residencial'),
(2, '48988776655', 'Celular'),
(3, '41977665544', 'Celular');

INSERT INTO Editora (Nome, CNPJ, Email) VALUES
('Ubisoft', '12.345.678/0001-99', 'contato@ubisoft.com'),
('Electronic Arts', '98.765.432/0001-55', 'ea@ea.com'),
('Rockstar Games', '11.222.333/0001-44', 'support@rockstar.com');

INSERT INTO Jogo (Nome, Plataforma, Preco, Estoque, id_Editora) VALUES
('Assassin''s Creed Valhalla', 'PC', 199.90, 15, 1),
('FIFA 24', 'PS5', 299.90, 20, 2),
('GTA V', 'Xbox', 149.90, 12, 3),
('Battlefield 2042', 'PC', 249.90, 10, 2),
('Far Cry 6', 'PS5', 229.90, 8, 1);

INSERT INTO Pedido (id_Cliente, Data, Valor) VALUES
(1, '2025-11-20', 349.80),
(2, '2025-11-21', 149.90),
(3, '2025-11-22', 299.90);

INSERT INTO Pedido_Jogo (id_Pedido, id_Jogo, Quantidade) VALUES
-- Pedido 1 (Cliente Lucas)
(1, 1, 1),  -- Assassin's Creed Valhalla
(1, 3, 1),  -- GTA V

-- Pedido 2 (Cliente Mariana)
(2, 3, 1),  -- GTA V

-- Pedido 3 (Cliente Pedro)
(3, 2, 1);  -- FIFA 24