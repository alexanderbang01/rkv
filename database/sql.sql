DROP DATABASE if exists rkv;
CREATE DATABASE rkv;
USE rkv;

CREATE TABLE users(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    department VARCHAR(255),
    email VARCHAR(255),
    phone BIGINT,
    code VARCHAR(255),
    amount INT NOT NULL DEFAULT 0
);

-- Existing users data
INSERT INTO users (name, department, email, phone, code) VALUES ("Lene Kudahl", "LOP", "leku@mercantec.dk", 89503587, "322jbhrk8c");
INSERT INTO users (name, department, email, phone, code) VALUES ("Bjarne Rasmussen", "Teknologi & Energi", "bjra@edu.mercantec.dk", 21655075, "ug2pw94nwt");
INSERT INTO users (name, department, email, phone, code) VALUES ("Peter Handby", "Bygge & Anlæg", "phan@mercantec.dk", 40415367, "y49byws4rx");
INSERT INTO users (name, department, email, phone, code) VALUES ("Nynne Stephansen ", "Bygge & Anlæg", "nyns@mercantec.dk", 23434273, "xe69a9sf8n");
INSERT INTO users (name, department, email, phone, code) VALUES ("Lars Milter Jensen", "Teknologi & Energi", "lapj@mercantec.dk", 51324276, "p374s2s3fw");
INSERT INTO users (name, department, email, phone, code) VALUES ("Michael Kabel Pedersen", "Teknologi & Energi", "mikp@mercantec.dk", 24954770, "v29v27nxn9");
INSERT INTO users (name, department, email, phone, code) VALUES ("Henrik Thomsen", "Teknologi & Energi", "heth@mercantec.dk", 30539361, "j5bfz789z4");
INSERT INTO users (name, department, email, phone, code) VALUES ("Kim Guldholt", "Teknologi & Energi", "kigu@mercantec.dk", 61140679, "h7jfe5wm78");
INSERT INTO users (name, department, email, phone, code) VALUES ("Poul Erik Sørensen", "Teknologi & Energi", "poes@mercantec.dk", 23230745, "z3m868j8m2");
INSERT INTO users (name, department, email, phone, code) VALUES ("Christian Winther", "Teknologi & Energi", "chwk@mercantec.dk", 40836565, "nks3b97f27");
INSERT INTO users (name, department, email, phone, code) VALUES ("Julie Aaberg Andersen", "Teknologi & Energi", "juaa@mercantec.dk", 22707322, "a4q4beb4x8");
INSERT INTO users (name, department, email, phone, code) VALUES ("Thomas Kromann Jensen", "Teknologi & Energi", "tkje@mercantec.dk", 42796888, "773b3zpwzr");
INSERT INTO users (name, department, email, phone, code) VALUES ("Jesper Guldager Madsen", "Teknologi & Energi", "jegm@mercantec.dk", 21367448, "91y6czkj89");
INSERT INTO users (name, department, email, phone, code) VALUES ("Anne-Line Dige Søndergaard", "Teknologi & Energi", "anso@mercantec.dk", 28110230, "t352us7rez");
INSERT INTO users (name, department, email, phone, code) VALUES ("Per Tegtmeier", "Teknologi & Energi", "pete@mercantec.dk", 20167856, "un982byw23");
INSERT INTO users (name, department, email, phone, code) VALUES ("Pia Betina Meyer", "Gastronomi & Sundhed", "pime@mercantec.dk", 50851083, "658xuxa57t");
INSERT INTO users (name, department, email, phone, code) VALUES ("Søren Damgaard", "Business", "sorm@mercantec.dk", 21596861, "4ghtf5km84");
INSERT INTO users (name, department, email, phone, code) VALUES ("Lillian Vester", "Business", "live@mercantec.dk", 92433462, "a3z2wgbc8m");
INSERT INTO users (name, department, email, phone, code) VALUES ("Jeanette Reslow", "LOP", "jere@mercantec.dk", 89503581, "p7n98267sk");
INSERT INTO users (name, department, email, phone, code, amount) VALUES ("Alexander Bang", "Teknologi & Energi", "alba@mercantec.dk", 20255711, "123", 47);
INSERT INTO users (name, department, email, phone, code) VALUES ("Karina Strand", "Gastronomi & Sundhed", "stra@mercantec.dk", 24750569, "3y1va2b2ow");
INSERT INTO users (name, department, email, phone, code) VALUES ("Mikkel Sehested Borre", "Teknologi & Energi", "mibs@mercantec.dk", 30328981, "ksq9h9mttu");
INSERT INTO users (name, department, email, phone, code) VALUES ("Mogens Frederiksen", "VK", "mogf@mercantec.dk", 24984793, "jrw6f3qlvb");
INSERT INTO users (name, department, email, phone, code) VALUES ("Martin Løndal", "VEU - konsulent", "malo@mercantec.dk", 20325281, "zxt4g7bqwm");

-- Education tables with last_updated tracking
CREATE TABLE educationtitle (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    link VARCHAR(500),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE education (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    eduId INT NOT NULL,
    name VARCHAR(255),
    length VARCHAR(255),
    euxLength VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (eduId) REFERENCES educationtitle(id) ON DELETE CASCADE
);

-- Education data with links moved to educationtitle
INSERT INTO educationtitle (title, link) VALUES ("Byggefag", "https://www.retsinformation.dk/eli/lta/2022/632");
INSERT INTO educationtitle (title, link) VALUES ("Automatik", "https://www.retsinformation.dk/eli/lta/2023/262");
INSERT INTO educationtitle (title, link) VALUES ("Bygningsmaler", "https://www.retsinformation.dk/eli/lta/2023/232");
INSERT INTO educationtitle (title, link) VALUES ("IT", "https://www.retsinformation.dk/eli/lta/2023/312");
INSERT INTO educationtitle (title, link) VALUES ("Salgsassistent", "https://www.retsinformation.dk/eli/lta/2023/136");
INSERT INTO educationtitle (title, link) VALUES ("Elektronikfag", "https://www.retsinformation.dk/eli/lta/2022/819");
INSERT INTO educationtitle (title, link) VALUES ("Elektronikfagtekniker", "https://www.retsinformation.dk/eli/lta/2022/599");
INSERT INTO educationtitle (title, link) VALUES ("Ernæringsassistent", "https://www.retsinformation.dk/eli/lta/2023/275");
INSERT INTO educationtitle (title, link) VALUES ("handelsass., salg", "https://www.retsinformation.dk/eli/lta/2023/137");
INSERT INTO educationtitle (title, link) VALUES ("Industrioperatør", "https://www.retsinformation.dk/eli/lta/2022/679");
INSERT INTO educationtitle (title, link) VALUES ("Industri- og maskinteknik", "https://www.retsinformation.dk/eli/lta/2023/264");
INSERT INTO educationtitle (title, link) VALUES ("Økonomi", "https://www.retsinformation.dk/eli/lta/2023/135");
INSERT INTO educationtitle (title, link) VALUES ("Offentlig administration", "https://www.retsinformation.dk/eli/lta/2023/135");
INSERT INTO educationtitle (title, link) VALUES ("Administration", "https://www.retsinformation.dk/eli/lta/2023/135");
INSERT INTO educationtitle (title, link) VALUES ("Bilmekaniker", "https://www.retsinformation.dk/eli/lta/2023/249");
INSERT INTO educationtitle (title, link) VALUES ("Smedefag", "https://www.retsinformation.dk/eli/lta/2023/269");
INSERT INTO educationtitle (title, link) VALUES ("Gulvlægger", "https://www.retsinformation.dk/eli/lta/2023/234");
INSERT INTO educationtitle (title, link) VALUES ("Tømrer", "https://www.retsinformation.dk/eli/lta/2023/234");
INSERT INTO educationtitle (title, link) VALUES ("VVS- og energispecialist", "https://www.retsinformation.dk/eli/lta/2023/233");
INSERT INTO educationtitle (title, link) VALUES ("Gastronom", "https://www.retsinformation.dk/eli/lta/2023/227");
INSERT INTO educationtitle (title, link) VALUES ("Eventkoordinator", "https://www.retsinformation.dk/eli/lta/2023/139");
INSERT INTO educationtitle (title, link) VALUES ("Bager og konditor", "https://www.retsinformation.dk/eli/lta/2024/165");
INSERT INTO educationtitle (title, link) VALUES ("Anlægsgartner", "https://www.retsinformation.dk/eli/lta/2024/166");

INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (1, "Anlægsstruktør", "3½– 4 år", "4- 4½ år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (1, "Bygningsstruktør", "3½– 4 år", "4- 4½ år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (2, "Automatikmontør", "2 år", "Nej");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (2, "Automatiktekniker", "4 år", "4½ år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (3, "Bygningsmaler", "3 år 3 md – 3 år 9 md", "3 år og 11 md - 4 år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (4, "IT-supporter", "2½ år", "Nej");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (4, "Datatekniker med speciale i infrastruktur", "5 år", "5½ år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (4, "Datatekniker med speciale i programmering", "5 år", "5½ år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (5, "Salgsassistent", "2½ år", "Studiekompetencegivende forløb: + 40 uger");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (6, "Elektriker 1", "4 år", "4½ år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (6, "Elektriker 2", "4½ år", "Nej");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (7, "Elektronikfagtekniker", "4 år", "4½ år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (8, "Ernæringsassistent", "3 år og 2 md", "3 år og 8 md");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (9, "Handelsass., salg", "2½ år", "Studiekompetencegivende forløb: + 40 uger");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (10, "Industrioperatør", "2½ år", "Nej");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (11, "Industriassistent", "2 år", "Nej");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (11, "Industritekniker - maskin", "4 år", "4½ år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (12, "Økonomi", "3½- 4 år Obligatorisk EUX", "Obligatorisk");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (13, "Offentlig administration", "3½- 4 år Obligatorisk EUX", "Obligatorisk");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (14, "Administration", "3½- 4 år Obligatorisk EUX", "Obligatorisk");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (15, "Personvognsmontør", "2 år", "Nej");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (15, "Personvognsmekaniker", "4 år", "4½ år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (16, "Smed (bearbejdning)", "2 år", "Nej");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (16, "Klejnsmed", "4 år", "4½ år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (17, "Gulvlægger", "3½– 4 år", "4- 4½ år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (18, "Tømrer", "3½– 4 år", "4- 4½ år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (19, "VVS- og energispecialist", "4 år", "4 år og 3 md");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (20, "Gastronomassistent", "1½ år", "Nej");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (20, "Smørrebrød og catering", "3 år", "3 år og 8 md");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (20, "Kok", "3 år og 9 md", "4 år og 3 md");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (5, "Digital handel", "2½ år", "3 ½ - 4 år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (5, "Convenience", "2½ år", "3 ½ - 4 år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (5, "Blomsterdekoratør", "2½ år", "3 ½ - 4 år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (5, "Dekoratør", "2½ år", "3 ½ - 4 år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (9, "Logistikassistent", "2½ år", "3 ½ - 4 år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (9, "Indkøbsassistent", "2½ år", "3 ½ - 4 år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (9, "Digital handel", "2½ år", "3 ½ - 4 år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (12, "Revision", "3½- 4 år Obligatorisk EUX", "Obligatorisk");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (21, "Eventkoordinator", "2½ år", "3 ½ - 4 år");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (22, "Bagværker", "3 år", "Nej");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (22, "Detailbager", "4 år og 6 md", "Nej");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (22, "Håndværksbager", "4 år og 6 md", "Nej");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (22, "Konditor", "4 år og 6 md", "Nej");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (23, "Anlægsgartner, anlægsteknik", "4 år", "4 år og 6 md");
INSERT INTO education (eduId, name, LENGTH, euxLength) VALUES (23, "Anlægsgartner, plejeteknik", "4 år", "4 år og 6 md");

-- Aktivitets tabel for at logge RKV aktiviteter
CREATE TABLE rkv_activities (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    student_name VARCHAR(255) NOT NULL,
    education_title_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL DEFAULT 'created',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (education_title_id) REFERENCES educationtitle(id) ON DELETE CASCADE
);

-- Placeholder data for RKV aktiviteter (existing data)
INSERT INTO rkv_activities (user_id, student_name, education_title_id, action_type, description, created_at) VALUES 
(20, "Lars Andersen", 7, "created", "RKV oprettet for Elektronikfagtekniker", "2025-06-20 09:15:30"),
(20, "Maria Pedersen", 4, "created", "RKV oprettet for Datatekniker", "2025-06-21 14:22:15"),
(20, "Thomas Nielsen", 2, "created", "RKV oprettet for Automatikmontør", "2025-06-22 10:45:22"),
(20, "Emma Christensen", 7, "created", "RKV oprettet for Elektronikfagtekniker", "2025-06-22 16:30:45"),
(20, "Mikkel Hansen", 11, "created", "RKV oprettet for Industritekniker", "2025-06-23 08:20:10"),
(2, "Anne Larsen", 1, "created", "RKV oprettet for Tømrer", "2025-06-18 11:30:15"),
(2, "Peter Møller", 19, "created", "RKV oprettet for VVS-specialist", "2025-06-19 14:45:20"),
(16, "Sofie Hansen", 20, "created", "RKV oprettet for Kok", "2025-06-17 16:20:30"),
(16, "Michael Berg", 6, "created", "RKV oprettet for Elektriker", "2025-06-16 10:15:45"),
(17, "Louise Dahl", 12, "created", "RKV oprettet for Økonomi", "2025-06-15 13:25:10"),
(17, "Jakob Storm", 22, "created", "RKV oprettet for Bager", "2025-06-14 09:40:25"),
(18, "Nina Frost", 8, "created", "RKV oprettet for Ernæringsassistent", "2025-06-13 15:50:35"),
(3, "Oliver Skov", 7, "created", "RKV oprettet for Elektronikfagtekniker", "2025-06-12 09:15:30"),
(4, "Emma Nielsen", 4, "created", "RKV oprettet for IT-supporter", "2025-06-11 14:22:15"),
(5, "Lucas Andersen", 2, "created", "RKV oprettet for Automatiktekniker", "2025-06-10 10:45:22");