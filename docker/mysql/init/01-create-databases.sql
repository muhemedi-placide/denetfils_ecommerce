CREATE DATABASE IF NOT EXISTS denetfils_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS denetfils_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON denetfils_api.* TO 'denetfils'@'%';
GRANT ALL PRIVILEGES ON denetfils_web.* TO 'denetfils'@'%';
FLUSH PRIVILEGES;
