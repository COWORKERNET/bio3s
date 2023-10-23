--
-- Table structure for table `banners`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `banners` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `type` tinyint(4) NOT NULL COMMENT 'type. 0:image, 1:movie',
  `ko` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0->ko, 1->en',
  `title` varchar(40) DEFAULT NULL,
  `content` varchar(50) DEFAULT NULL COMMENT 'content',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_banners_1` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='banner';

--
-- Table structure for table `boards`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `boards` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:top, 2:delete',
  `type` tinyint(4) NOT NULL COMMENT 'type. 0:news, 1:notice',
  `ko` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0->ko, 1->en',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT 'count',
  `title` varchar(255) NOT NULL COMMENT 'title',
  `content` longtext NOT NULL COMMENT 'content',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_boards_1` (`status`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='board';

--
-- Table structure for table `certificates`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `certificates` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `type` varchar(20) DEFAULT NULL COMMENT 'type. 일반 텍스트',
  `ko` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0->ko, 1->en',
  `register_date` date DEFAULT NULL COMMENT 'register_date. 일자',
  `doc_status` varchar(20) DEFAULT NULL COMMENT 'doc_status. 진행상태',
  `number` varchar(100) DEFAULT NULL COMMENT 'number. 인증번호',
  `content` varchar(255) DEFAULT NULL COMMENT 'content. 내용',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_certificates_1` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='certificates';

--
-- Table structure for table `faqs`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `faqs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `ref_products_id` bigint(20) NOT NULL COMMENT 'product id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `ko` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0->ko, 1->en',
  `title` varchar(100) DEFAULT NULL COMMENT 'title',
  `content` varchar(255) DEFAULT NULL COMMENT 'content',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_faqs_1` (`status`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='faqs';

--
-- Table structure for table `files`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `files` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `type` varchar(45) NOT NULL COMMENT 'type',
  `ref_type_id` bigint(20) NOT NULL COMMENT 'ref_type_id',
  `filename` varchar(255) NOT NULL COMMENT 'filename',
  `origin_filename` varchar(255) NOT NULL COMMENT 'origin_filename',
  `filesize` int(11) NOT NULL COMMENT 'filesize',
  `ext` varchar(10) NOT NULL COMMENT 'ext',
  `fileAddr` varchar(255) NOT NULL COMMENT 'fileAddr',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_files_1` (`status`,`type`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='files';

--
-- Table structure for table `galleries`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `galleries` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'type. 0:Echo, 1:Good, 2:Clean',
  `ko` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0->ko, 1->en',
  `content` varchar(50) DEFAULT NULL COMMENT 'content',
  `register_date` date DEFAULT NULL COMMENT 'register_date',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_galleries_1` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='galleries';

--
-- Table structure for table `histories`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `histories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `ref_history_id` bigint(20) DEFAULT NULL COMMENT 'ref_history_id. year range',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `ko` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0->ko, 1->en',
  `year` int(11) DEFAULT NULL COMMENT 'year',
  `month` int(11) DEFAULT NULL COMMENT 'month',
  `content` varchar(255) DEFAULT NULL COMMENT 'content',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_histories_1` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='history';

--
-- Table structure for table `historyCategories`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `historyCategories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `ko` tinyint(1) NOT NULL DEFAULT '0',
  `year_start` int(11) DEFAULT NULL COMMENT 'year_start',
  `year_end` int(11) DEFAULT NULL COMMENT 'year_end',
  `content` varchar(100) NOT NULL COMMENT 'content',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='hisoryCategory';

--
-- Table structure for table `migrations`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `oauth_access_tokens`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_access_tokens_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `oauth_auth_codes`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_auth_codes_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `oauth_clients`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `oauth_clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `oauth_clients_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `oauth_personal_access_clients`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `oauth_refresh_tokens`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `patents`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `patents` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `title` varchar(100) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `type` varchar(45) NOT NULL COMMENT '0:국내출원 1:국내등록 2:해외출원 3:해외등록',
  `ko` tinyint(4) NOT NULL DEFAULT '0',
  `number` varchar(100) NOT NULL COMMENT 'number. 출원/등록 번호',
  `writer` varchar(45) DEFAULT NULL COMMENT 'writer. 출원/등록인',
  `register_date` date DEFAULT NULL COMMENT 'register_date. 출원/등록일',
  `country` varchar(45) DEFAULT NULL COMMENT 'country. 등록국가',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_patents_1` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='patents';

--
-- Table structure for table `personal_access_tokens`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `popups`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `popups` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `ko` tinyint(4) NOT NULL DEFAULT '0',
  `link` varchar(255) NOT NULL COMMENT 'link. 링크',
  `title` varchar(70) NOT NULL COMMENT 'title. 제목',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_popups_1` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='popups';

--
-- Table structure for table `productCertificates`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `productCertificates` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `ko` tinyint(4) NOT NULL DEFAULT '0',
  `ref_certificates_id` bigint(20) NOT NULL COMMENT 'ref_certificates_id',
  `ref_products_id` bigint(20) NOT NULL COMMENT 'ref_products_id',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_productSocials_1` (`ref_products_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='productCertificates';

--
-- Table structure for table `productProcurement`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `productProcurement` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `ref_procurementList_id` bigint(20) NOT NULL COMMENT 'productProcurementList_id',
  `type` tinyint(1) NOT NULL COMMENT '0: 혁신장터, 1: 벤처나라, 2: 학교장터, 3: 동반성장물, 4: 우수조달',
  `ko` tinyint(4) NOT NULL DEFAULT '0',
  `tags` varchar(255) DEFAULT NULL COMMENT 'tags',
  `title` varchar(100) DEFAULT NULL COMMENT 'title',
  `short_content` varchar(255) DEFAULT NULL COMMENT 'short_content',
  `deliverGuide` varchar(255) DEFAULT NULL COMMENT 'deliverGuide',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_productProcurement_1` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='productProcurement';

--
-- Table structure for table `productProcurementGuideList`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `productProcurementGuideList` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `ref_procurement_id` bigint(20) NOT NULL COMMENT 'ref_procurement_id',
  `ref_procurement_type_id` bigint(20) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'type. 0:구매방법, 1:구매옵션, 2:홍보용 라벨 부착 예시, 3: 사진1, 4: 사진2, 5: 사진3, 6: detail center image, 7: contract_document_file',
  `ko` tinyint(4) NOT NULL DEFAULT '0',
  `content` varchar(100) DEFAULT ' ' COMMENT 'content',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_productProcurementGuideList_2` (`ref_procurement_id`),
  KEY `IX_productProcurementGuideList_1` (`status`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `productProcurementList`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `productProcurementList` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `ko` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_productProcurementList_1` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='productProcurementList';

--
-- Table structure for table `productProcurementTableColumn`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `productProcurementTableColumn` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `row` bigint(20) NOT NULL COMMENT 'row. row in column',
  `type` tinyint(1) NOT NULL COMMENT '0:text, 1:link',
  `ko` tinyint(4) NOT NULL DEFAULT '0',
  `ref_table_header_id` bigint(20) NOT NULL COMMENT 'ref_table_header_id. 테이블 열 참조 Id',
  `ref_procurement_id` bigint(20) NOT NULL COMMENT 'ref_procurement_id. 상품 참조 Id',
  `ref_procurement_type_id` tinyint(4) NOT NULL,
  `content` varchar(100) DEFAULT NULL COMMENT 'content. 컬럼 내용',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_productProcurementTableColumn_1` (`status`,`row`,`ref_table_header_id`,`ref_procurement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='productProcurementTableColumn';

--
-- Table structure for table `productProcurementTableColumnHeader`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `productProcurementTableColumnHeader` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id. column ref to this id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `ref_procurement_id` bigint(20) NOT NULL COMMENT 'ref_procurement_id',
  `ko` tinyint(4) NOT NULL DEFAULT '0',
  `content` varchar(100) DEFAULT NULL COMMENT 'content',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_productProcurementTableColumnHeader_1` (`status`,`ref_procurement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='productProcurementTableColumnHeader';

--
-- Table structure for table `products`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `products` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:main, 2:delete',
  `ko` tinyint(4) NOT NULL DEFAULT '0',
  `category` varchar(20) NOT NULL,
  `tags` varchar(255) DEFAULT NULL COMMENT 'tags',
  `title` varchar(100) NOT NULL COMMENT 'title',
  `sel_link` varchar(255) DEFAULT NULL COMMENT 'sel_link',
  `short_content` varchar(255) DEFAULT NULL COMMENT 'short_content',
  `content` longtext DEFAULT NULL COMMENT 'content',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_products_1` (`status`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='products';

--
-- Table structure for table `services`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `services` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:complete,  2:delete',
  `name` varchar(50) NOT NULL COMMENT 'name',
  `phone` varchar(13) NOT NULL COMMENT 'phone',
  `company` varchar(50) NOT NULL COMMENT 'company',
  `email` varchar(100) NOT NULL COMMENT 'email',
  `category` varchar(100) NOT NULL COMMENT 'category',
  `content` longtext NOT NULL COMMENT 'content',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_services_1` (`status`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='services';

--
-- Table structure for table `socials`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `socials` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `ref_products_id` bigint(20) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `type` tinyint(4) NOT NULL COMMENT 'type. 0:youtube, 1:instagram, 2:products',
  `ko` tinyint(4) NOT NULL DEFAULT '0',
  `link` varchar(255) NOT NULL COMMENT 'link',
  `title` varchar(50) NOT NULL COMMENT 'title',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`),
  KEY `IX_socials_1` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='socials';

--
-- Table structure for table `users`
-- Created with MySQL Version 10.6.12
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status. 0:active, 1:delete',
  `name` varchar(45) NOT NULL COMMENT 'name',
  `email` varchar(100) NOT NULL COMMENT 'email',
  `password` varchar(255) NOT NULL COMMENT 'password',
  `created_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'created_at',
  `updated_at` datetime NOT NULL DEFAULT '0-00-00 00:00:00' COMMENT 'updated_at',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='user';

