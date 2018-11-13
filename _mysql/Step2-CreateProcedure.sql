CREATE DEFINER=`root`@`localhost` PROCEDURE `logRequest`(
	IN in_url	VARCHAR(255),
	IN in_username	VARCHAR(16),
	IN in_userip	VARCHAR(15),
	IN in_phpid	VARCHAR(255),
	IN in_sessionid	VARCHAR(255)
)
	COMMENT 'Log a HTTP request'
BEGIN
	INSERT INTO BW_Log
	(	Username,		UserIP,		RequestPage,	PHPID,		SessionID		) VALUES
	(	in_username,	in_userip,	in_url,			in_phpid,	in_sessionid	);
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `pageCreate`(
	IN in_url	CHAR(255),
	IN in_title	VARCHAR(255),
	IN in_author	VARCHAR(255),
	IN in_category	VARCHAR(255),
	IN in_mime	VARCHAR(255),
	IN in_status	VARCHAR(255)
)
	COMMENT 'Put all the param to create a page, NULL is NOT allowed'
BEGIN
	INSERT INTO BW_Sitemap
	(	URL,	Title,		Author,		Category,		MIME,		`Status`	) VALUES
	(	in_url,	in_title,	in_author,	in_category,	in_mime,	in_status	);
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `pageDelete`(
	IN in_url	CHAR(255)
)
	COMMENT 'Delete a page by the url'
BEGIN
	DELETE FROM BW_Sitemap WHERE BW_Sitemap.URL = in_url;
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `pageGetFull`(
	IN in_url	CHAR(255)
)
	COMMENT 'Get * by url'
BEGIN
	SELECT * FROM BW_Sitemap S WHERE S.URL = in_url;
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `pageModify`(
	IN in_url		CHAR(255),
	IN in_mime		VARCHAR(255),
	IN in_title		VARCHAR(255),
	IN in_keywords		VARCHAR(255),
	IN in_description	VARCHAR(4096),
	IN in_category		VARCHAR(255),
	IN in_author		VARCHAR(255),
	IN in_templatemain	VARCHAR(255),
	IN in_templatesub	VARCHAR(255),
	IN in_data		LONGTEXT,
	IN in_binary		LONGBLOB,
	IN in_json		LONGTEXT,
	IN in_copyright		VARCHAR(255),
	IN in_status		CHAR(1)
)
	COMMENT 'Update a page. For all params, put "new data" to modify that data, or NULL if do not modify.'
BEGIN
	UPDATE BW_Sitemap S
	SET
		S.URL		= in_url,
		S.MIME		= IFNULL(	in_mime,		S.MIME		),
		S.Title		= IFNULL(	in_title,		S.Title		),
		S.Keywords	= IFNULL(	in_keywords,		S.Keywords	),
		S.Description	= IFNULL(	in_description,		S.Description	),
		S.Category	= IFNULL(	in_category,		S.Category	),
		S.Author	= IFNULL(	in_author,		S.Author	),
		S.TemplateMain	= IFNULL(	in_templatemain,	S.TemplateMain	),
		S.TemplateSub	= IFNULL(	in_templatesub,		S.TemplateSub	),
		S.`Data`	= IFNULL(	in_data,		S.`Data`	),
		S.`Binary`	= IFNULL(	in_binary,		S.`Binary`	),
		S.`JSON`	= IFNULL(	in_json,		S.`JSON`	),
		S.Copyright	= IFNULL(	in_copyright,		S.Copyright	),
		S.`Status`	= IFNULL(	in_status,		S.`Status`	),
		S.LastModify	= CURRENT_TIMESTAMP,
		S.Version	= S.Version + 1
	WHERE S.URL = in_url;
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `pageModifyIDE`(
	IN in_url			CHAR(255),
	IN in_keywords		VARCHAR(255),
	IN in_description	VARCHAR(4096),
	IN in_copyright		VARCHAR(255),
	IN in_status		CHAR(1)
)
	COMMENT 'Update pages with the URL prefix. For all params, put "new data" to modify that data, or NULL if do not modify. Copyright will not be modified if orginal is begin with "Reference="'
BEGIN
	UPDATE BW_Sitemap S
	SET
		S.Keywords	= IFNULL(	in_keywords,		S.Keywords	),
		S.Description	= IFNULL(	in_description,		S.Description	),
		S.Copyright	= IF(
			SUBSTRING(`Copyright`,1,10) = 'Reference=',
			Copyright,
			IFNULL(in_copyright,S.Copyright)
		),
		S.`Status`	= IFNULL(	in_status,		S.`Status`	),
		S.LastModify	= CURRENT_TIMESTAMP,
		S.Version	= S.Version + 1
	WHERE S.URL LIKE in_url;
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `pagesCount`(
	IN in_category	VARCHAR(255),
	IN in_special	BOOLEAN,
	IN in_ap	BOOLEAN
)
	COMMENT 'Count HTML pages under a category. Use "a,b,c" for multi category or NULL for all category. Param special and ap means include S, A, P status page'
BEGIN
	SELECT COUNT(*) X
	FROM BW_Sitemap S
	WHERE
		S.MIME = 'text/html' AND
		(in_category IS NULL OR FIND_IN_SET(S.Category,in_category)) AND
		(in_special OR S.`Status` <> 'S') AND
		(in_ap OR S.`Status` NOT IN ('A','P'));
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `pagesRecent`(
	IN in_count		INT,
	IN in_pageoffset	INT,
	IN in_category		VARCHAR(255),
	IN in_special		BOOLEAN
)
	COMMENT 'Get HTML pages in a category, sort by LastModify desc. Use "a,b,c" for multi category or NULL for all category. Param special means include S status page. A, P status page will NOT be include'
BEGIN
	SELECT
		S.URL				URL,
		S.Title				Title,
		S.Keywords			Keywords,
		S.Description			Description,
		S.Author			Author,
		(SELECT U.Nickname FROM BW_User U WHERE U.Username = S.Author) AS AuthorNickname,
		S.LastModify			LastModify,
		S.`Status`			`Status`,
		S.`JSON`->>'$.poster'		Poster 
	FROM BW_Sitemap S
	WHERE
		S.MIME = 'text/html' AND
		S.`Status` NOT IN ('A','P') AND
		(in_category IS NULL OR FIND_IN_SET(S.Category,in_category)) AND
		(in_special OR S.`Status` <> 'S')
	ORDER BY S.LastModify DESC
	LIMIT in_count OFFSET in_pageoffset;
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `pagesSearch`(
	IN in_count		INT,
	IN in_pageoffset	INT,
	IN in_keyword		VARCHAR(32),
	IN in_category		VARCHAR(256)
)
	COMMENT 'Search text/html records from BW_Sitemap, sort by LastModify desc. Use "a,b,c" for multi category or NULL for all category. Param special means include S status page.'
BEGIN
	SELECT
		S.URL				URL,
		S.Title				Title,
		S.Keywords			Keywords,
		S.Description			Description,
		S.Author			Author,
		S.LastModify			LastModify,
		S.`Status`			`Status`,
		S.`JSON`->>'$.poster'		Poster
	FROM BW_Sitemap S
	WHERE
		S.MIME = 'text/html' AND
		S.`Status` NOT IN ('A', 'P', 'S') AND
		(in_category IS NULL OR FIND_IN_SET(S.Category,in_category)) AND
		MATCH(S.Title,S.Keywords,S.Description,S.`Data`) AGAINST (in_keyword IN natural language mode)
	ORDER BY MATCH(S.Title,S.Keywords,S.Description,S.`Data`) AGAINST (in_keyword WITH QUERY EXPANSION) DESC
	LIMIT in_count OFFSET in_pageoffset;
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `pageSubwork`(
	IN in_work	CHAR(255)
)
	COMMENT 'Get URL and LastModify that the URL is prefixed by param'
BEGIN
	SELECT URL, LastModify FROM BW_Sitemap WHERE URL LIKE in_work;
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `siteConfig`()
	COMMENT 'Get site config saved in BW_Config'
BEGIN
	SELECT * FROM BW_Config;
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `sitemap`(
	IN in_count		INT,
	IN in_pageoffset	INT
)
	COMMENT 'List pages in BW_Sitemap, S, A, P status page will NOT be included'
BEGIN
	SELECT 
		S.URL			URL,
		S.LastModify	LastModify
	FROM BW_Sitemap S
	WHERE S.`Status` NOT IN ('S','A','P')
	LIMIT in_count OFFSET in_pageoffset;
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `siteOSUpdate`(
	IN in_token	VARCHAR(255)
)
	COMMENT 'Update object storage config, saved in BW_Config. This procedure is for CONOHA Object Storage system ONLY'
BEGIN
	UPDATE BW_Config C SET C.`Value` = in_token WHERE C.`Key` = 'ObjStoToken';
	UPDATE BW_Config C SET C.`Value` = UNIX_TIMESTAMP() + 72000 WHERE C.`Key` = 'ObjStoExpire';
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `userActive`(
	IN in_username		CHAR(16)
)
	COMMENT 'Update LastActiveTime of the user to now'
BEGIN
	UPDATE BW_User U
	SET
		U.LastActiveTime = CURRENT_TIMESTAMP
	WHERE U.Username = in_username;
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `userModify`(
	IN in_username	CHAR(16),
	IN in_nickname 	CHAR(16) CHARSET utf8,
	IN in_group	CHAR(16),
	IN in_password	CHAR(32),
	IN in_email	VARCHAR(128),
	IN in_data	LONGTEXT,
	IN in_photo	BLOB
)
	COMMENT 'Update user info. For all params, put "new data" to modify that data, or NULL if do not modify.'
BEGIN
	UPDATE BW_User U
	SET
		U.Nickname	= IFNULL(	in_nickname,	U.Nickname	),
		U.`Group`	= IFNULL(	in_group,	U.`Group`	),
		U.`Password`	= IFNULL(	in_password,	U.`Password`	),
		U.Email		= IFNULL(	in_email,	U.Email		),
		U.`Data`	= IFNULL(	in_nickname,	U.`Data`	),
		U.Photo		= IFNULL(	in_photo,	U.Photo		)
	WHERE U.Username = username;
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `userPages`(
	IN in_username	VARCHAR(255),
	IN in_category	VARCHAR(255)
)
	COMMENT 'Get publishs by username, sort by LastModify desc. Use "a,b,c" for multi category or NULL for all category.'
BEGIN
	SELECT
		S.URL	URL,
		S.Title	Title
	FROM BW_Sitemap S
	WHERE
		S.Author = in_username AND
		S.MIME = 'text/html' AND
		(in_category IS NULL OR FIND_IN_SET(S.Category,in_category))
	ORDER BY S.LastModify DESC;
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `userRegister`(
	IN in_username		CHAR(16),
	IN in_nickname		CHAR(16) CHARSET utf8,
	IN in_group		CHAR(16),
	IN in_password		CHAR(32),
	IN in_registerip	CHAR(15),
	IN in_photo		BLOB
)
	COMMENT 'Adding user into BW_User'
BEGIN
	INSERT INTO BW_User
	(	Username,	Nickname,	`Group`,	`Password`,	RegisterIP,	Photo		) VALUES
	(	in_username,	in_nickname,	in_group,	in_password,	in_registerip,	in_photo	);
END;



CREATE DEFINER=`root`@`localhost` PROCEDURE `userSearch`(
	IN in_username CHAR(16),
	IN in_nickname CHAR(16) CHARSET utf8
)
	COMMENT 'Search a user. If Params user is not NULL, get that user; otherwise, find user with Nickname contains param nickname'
BEGIN
	IF in_username IS NOT NULL THEN
		SELECT * FROM BW_User U WHERE U.Username = in_username;
	ELSE
		SELECT * FROM BW_User U WHERE U.Nickname LIKE CONCAT('%',in_nickname,'%');
	END IF;
END;