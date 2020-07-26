CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `deviceName` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `fileName` text NOT NULL,
  `MD5` text NOT NULL,
  `downloadCount` int(11) DEFAULT 0,
  `fileSize` text NOT NULL,
  `deviceName` int(11) NOT NULL,
  `fileType` int(11) NOT NULL,
  `baseName` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `releaseTypes` (
  `id` int(11) NOT NULL,
  `types` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fileType` (`fileType`),
  ADD KEY `deviceName` (`deviceName`);

ALTER TABLE `releaseTypes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `releaseTypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `files`
  ADD CONSTRAINT `deviceName` FOREIGN KEY (`deviceName`) REFERENCES `devices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fileType` FOREIGN KEY (`fileType`) REFERENCES `releaseTypes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;