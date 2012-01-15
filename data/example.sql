DROP TABLE IF EXISTS `item`;
CREATE TABLE `item` (
	`ID` varchar(255) NOT NULL,
	`NAME` varchar(255) DEFAULT NULL,
	`MANU` varchar(255) DEFAULT NULL,
	`WEIGHT` double(4,2) DEFAULT NULL,
	`PRICE` double(6,2) DEFAULT NULL,
	`POPULARITY` smallint(3) DEFAULT NULL,
	`INSTOCK` smallint(1) DEFAULT NULL,
	`INCLUDES` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `feature`;
CREATE TABLE `feature` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`item_id` varchar(255) NOT NULL,
	`description` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `item_category`;
CREATE TABLE `item_category` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`item_id` varchar(255) NOT NULL,
	`CATEGORY_ID` int(10) NOT NULL,
	PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`description` varchar(255) NOT NULL,
	PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--

INSERT INTO `category`
	( `id`, `description` )
VALUES
	( 1, 'electronics' ),
	( 2, 'music' ),
	( 3, 'camera' ),
	( 4, 'graphics card' );

--

SET @ID='MA147LL/A';

INSERT INTO `item`
	( `ID`, `NAME`, `MANU`, `WEIGHT`, `PRICE`, `POPULARITY`, `INSTOCK`, `INCLUDES` )
VALUES
	( @ID, 'Apple 60 GB iPod with Video Playback Black',  'Apple Computer Inc.', 5.5, 399.00, 10, 1, 'earbud headphones, USB cable' );

INSERT INTO `feature`
	( `item_id`, `description` )
VALUES
	( @ID, 'iTunes, Podcasts, Audiobooks' ),
	( @ID, 'Stores up to 15,000 songs, 25,000 photos, or 150 hours of video' ),
	( @ID, '2.5-inch, 320x240 color TFT LCD display with LED backlight' ),
	( @ID, 'Up to 20 hours of battery life' ),
	( @ID, 'Plays AAC, MP3, WAV, AIFF, Audible, Apple Lossless, H.264 video' );

INSERT INTO `item_category`
	( `item_id`, `CATEGORY_ID` )
VALUES
	( @ID, 1 ),
	( @ID, 2 );

--

SET @ID='9885A004';

INSERT INTO `item`
	( `ID`, `NAME`, `MANU`, `WEIGHT`, `PRICE`, `POPULARITY`, `INSTOCK`, `INCLUDES` )
VALUES
	( @ID, 'Canon PowerShot SD500', 'Canon Inc.', 6.4, 329.95, 7, 1, '32MB SD card, USB cable, AV cable, battery' );

INSERT INTO `feature`
	( `item_id`, `description` )
VALUES
	( @ID, '3x zoop, 7.1 megapixel Digital ELPH' ),
	( @ID, 'movie clips up to 640x480 @30 fps' ),
	( @ID, '2.0" TFT LCD, 118,000 pixels' ),
	( @ID, 'built in flash, red-eye reduction' );

INSERT INTO `item_category`
	( `item_id`, `CATEGORY_ID` )
VALUES
	( @ID, 1 ),
	( @ID, 3 );

--

SET @ID='EN7800GTX/2DHTV/256M';

INSERT INTO `item`
	( `ID`, `NAME`, `MANU`, `WEIGHT`, `PRICE`, `POPULARITY`, `INSTOCK`, `INCLUDES` )
VALUES
	( @ID, 'ASUS Extreme N7800GTX/2DHTV (256 MB)', 'ASUS Computer Inc.', 16, 479.95, 7, 0, NULL );

INSERT INTO `feature`
	( `item_id`, `description` )
VALUES
	( @ID, 'NVIDIA GeForce 7800 GTX GPU/VPU clocked at 486MHz' ),
	( @ID, '256MB GDDR3 Memory clocked at 1.35GHz' ),
	( @ID, 'PCI Express x16' ),
	( @ID, 'Dual DVI connectors, HDTV out, video input' );

INSERT INTO `item_category`
	( `item_id`, `CATEGORY_ID` )
VALUES
	( @ID, 1 ),
	( @ID, 4 );