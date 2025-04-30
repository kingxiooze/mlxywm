/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `admin_extension_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `admin_extension_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `version` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_extension_histories_name_index` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_extensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `admin_extensions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `is_enabled` tinyint(4) NOT NULL DEFAULT '0',
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_extensions_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `admin_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) NOT NULL DEFAULT '0',
  `order` int(11) NOT NULL DEFAULT '0',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uri` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extension` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `show` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_permission_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `admin_permission_menu` (
  `permission_id` bigint(20) NOT NULL,
  `menu_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `admin_permission_menu_permission_id_menu_id_unique` (`permission_id`,`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `admin_permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `http_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `http_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `order` int(11) NOT NULL DEFAULT '0',
  `parent_id` bigint(20) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_permissions_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_role_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `admin_role_menu` (
  `role_id` bigint(20) NOT NULL,
  `menu_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `admin_role_menu_role_id_menu_id_unique` (`role_id`,`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `admin_role_permissions` (
  `role_id` bigint(20) NOT NULL,
  `permission_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `admin_role_permissions_role_id_permission_id_unique` (`role_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_role_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `admin_role_users` (
  `role_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `admin_role_users_role_id_user_id_unique` (`role_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `admin_roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `admin_settings` (
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `admin_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_users_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `banners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片资源',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='轮播图';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chat_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `chat_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint(20) unsigned NOT NULL COMMENT '聊天室ID',
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `is_mute` tinyint(1) NOT NULL COMMENT '是否禁言',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='聊天室成员';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chat_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `chat_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint(20) unsigned NOT NULL COMMENT '聊天室ID',
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `content` json NOT NULL COMMENT '内容',
  `record_type` tinyint(4) NOT NULL COMMENT '类型,0=文本,1=图片,2=红包,',
  `redpacket_amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '红包金额',
  `redpacket_count` int(11) NOT NULL DEFAULT '0' COMMENT '红包人数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='聊天记录';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chat_redpackets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `chat_redpackets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `record_id` bigint(20) unsigned NOT NULL COMMENT '聊天记录ID',
  `amount` decimal(20,2) NOT NULL COMMENT '中奖金额',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='红包中奖记录';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chat_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `chat_rooms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `user_id` bigint(20) unsigned NOT NULL COMMENT '所属用户',
  `uplimit` int(11) NOT NULL COMMENT '最多邀请人数',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '公告',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '头像',
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'KEY',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='聊天室';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `coupons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `discount` decimal(8,2) NOT NULL DEFAULT '1.00' COMMENT '折扣比例',
  `expire_time` int(11) NOT NULL COMMENT '失效时长(小时)',
  `weights` int(11) NOT NULL COMMENT '权重',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `customer_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `customer_services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '地址',
  `account` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '账号',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `salesman_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '业务员码',
  `service_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '服务类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客服';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `group_purchase_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `group_purchase_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` bigint(20) unsigned NOT NULL COMMENT '商品ID',
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0' COMMENT '状态,0=拼团中,1=拼团成功,2=拼团失败',
  `expired_at` timestamp NULL DEFAULT NULL COMMENT '拼团过期时间',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT '购买份数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='拼团记录';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `item_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `item_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品分类';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `item_price_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `item_price_audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` bigint(20) unsigned NOT NULL COMMENT '商品ID',
  `before_price` decimal(20,2) NOT NULL COMMENT '修改前价格',
  `after_price` decimal(20,2) NOT NULL COMMENT '修改后价格',
  `amount` decimal(20,2) NOT NULL COMMENT '增减幅度',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片',
  `price` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `gain_per_day` decimal(20,2) DEFAULT '0.00' COMMENT '日收益金额',
  `gain_day_num` int(11) DEFAULT '0' COMMENT '可收益天数',
  `cashback` decimal(20,2) DEFAULT '0.00' COMMENT '返现金额(佣金)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `purchase_limit` int(11) NOT NULL DEFAULT '1' COMMENT '最大购买数量',
  `secondary_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '次要图片',
  `stock` int(10) unsigned DEFAULT '0' COMMENT '库存',
  `is_group_purchase` tinyint(1) DEFAULT '0' COMMENT '是否开启拼团',
  `group_people_count` int(11) DEFAULT '0' COMMENT '拼团人数',
  `is_sell` tinyint(1) DEFAULT '0' COMMENT '是否上架',
  `gp_start_time` timestamp NULL DEFAULT NULL COMMENT '拼团开始时间',
  `gp_end_time` timestamp NULL DEFAULT NULL COMMENT '拼团结束时间',
  `category_id` bigint(20) DEFAULT NULL COMMENT '分类ID',
  `logistics_hours` int(11) DEFAULT '0' COMMENT '物流周期(小时)',
  `item_no` tinyint(4) DEFAULT NULL COMMENT '商品编号',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '跳转链接',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '描述',
  `joined_count_display` int(11) NOT NULL DEFAULT '0' COMMENT '可控参团人数,非真实数据',
  `remain_sales_amount` int(11) NOT NULL DEFAULT '0' COMMENT '剩余售卖额度',
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '地区',
  `characteristic` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '商品特点',
  `auto_dec_stock` int(11) DEFAULT NULL COMMENT '自动扣除库存数量',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_earning_at_end` tinyint(4) DEFAULT '0' COMMENT '是否到期后领取所有收益',
  `presale_start_at` timestamp NULL DEFAULT NULL COMMENT '预售开始时间',
  `presale_end_at` timestamp NULL DEFAULT NULL COMMENT '预售结束时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `money_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `money_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `money` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '变动金额',
  `log_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '类型',
  `before_change` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '变动前余额',
  `item_id` bigint(20) unsigned DEFAULT NULL COMMENT '商品ID',
  `source_uid` bigint(20) unsigned DEFAULT NULL COMMENT '来源用户',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_item_id` bigint(20) unsigned DEFAULT NULL COMMENT '用户商品关联表ID',
  `balance_type` tinyint(4) DEFAULT NULL COMMENT '金额类型,1=普通余额,2=红包金,3=任务金',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='资金记录';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `news` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '简介',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '详情',
  `item_category_id` bigint(20) NOT NULL COMMENT '商品分类ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_usdt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `order_usdt` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `order_id` bigint(20) unsigned NOT NULL COMMENT '订单ID',
  `amount` decimal(20,2) NOT NULL COMMENT '金额',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片',
  `status` tinyint(4) NOT NULL COMMENT '状态,0=未审核,1=审核通过,2=审核失败',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单USDT支付';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '订单编号',
  `trade_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '支付平台订单编号',
  `user_id` bigint(20) DEFAULT NULL COMMENT '支付用户ID',
  `pay_type` tinyint(3) unsigned DEFAULT NULL COMMENT '支付方式:1=CSPAY,2=TRC-USDT,3=YTPAY',
  `goods_type` tinyint(3) unsigned DEFAULT NULL COMMENT '商品类型1=充值',
  `money` decimal(20,2) DEFAULT NULL COMMENT '实际支付金额',
  `price` decimal(20,2) NOT NULL COMMENT '商品总价',
  `pay_status` tinyint(3) unsigned DEFAULT NULL COMMENT '支付状态:1=已支付,2=未支付',
  `order_status` tinyint(3) unsigned DEFAULT NULL COMMENT '订单状态:1=待付款,2=已付款,3=交易取消,4=交易成功',
  `pay_time` timestamp NULL DEFAULT NULL COMMENT '支付时间',
  `payable_id` bigint(20) DEFAULT NULL COMMENT '关联ID',
  `payable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '关联类型',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pay_channels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `pay_channels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `pay_type` int(11) NOT NULL COMMENT '渠道代码',
  `sort` int(11) NOT NULL COMMENT '排序',
  `hidden_at` timestamp NULL DEFAULT NULL COMMENT '隐藏时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `public_notices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `public_notices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='弹出公告';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `review_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `review_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_item_id` bigint(20) unsigned NOT NULL COMMENT '用户商品表ID',
  `tmpl_id` bigint(20) unsigned NOT NULL COMMENT '评价模板表ID',
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '图片,可多张,以半角逗号分隔',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '完成情况文本',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态,0=未审核,1=审核通过,2=审核失败',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='评价记录';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `review_tmpls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `review_tmpls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` bigint(20) unsigned NOT NULL COMMENT '商品ID',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '评价内容',
  `image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '评价图片',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='评价模板';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sales_admin_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `sales_admin_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) NOT NULL DEFAULT '0',
  `order` int(11) NOT NULL DEFAULT '0',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uri` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extension` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `show` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sales_admin_permission_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `sales_admin_permission_menu` (
  `permission_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `admin_permission_menu_permission_id_menu_id_index` (`permission_id`,`menu_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sales_admin_role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `sales_admin_role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `admin_role_permissions_role_id_permission_id_index` (`role_id`,`permission_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `settings_key_index` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='设置';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sign_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `sign_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `reward` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '获得金额',
  `signed_at` timestamp NULL DEFAULT NULL COMMENT '签到时间',
  `duration_day` int(11) DEFAULT '0' COMMENT '连续签到天数',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='签到记录';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `text_contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `text_contents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `types` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '顺序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_activite_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `user_activite_codes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '激活码',
  `activite_user` bigint(20) unsigned DEFAULT NULL COMMENT '激活用户',
  `activited_at` timestamp NULL DEFAULT NULL COMMENT '激活时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户激活码';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_bankcard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `user_bankcard` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户ID',
  `bank_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '银行名称',
  `card_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '银行卡号',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '姓名',
  `mobile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '手机号',
  `ifsc_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IFSC编码',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邮箱',
  `subbranch` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分行支行',
  `wallet_chain` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '钱包所属链',
  `wallet_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '钱包地址',
  `bank_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '银行编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户银行卡';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_cashback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `user_cashback` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `pay_uid` bigint(20) unsigned NOT NULL COMMENT '消费用户ID',
  `item_id` bigint(20) unsigned NOT NULL COMMENT '商品ID',
  `pay_amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '消费金额',
  `back_amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '返现金额',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态,0=未领取,1=领取',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `log_type` tinyint(4) DEFAULT NULL COMMENT '返现来源,与资金记录同名字段一致',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='返现';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `user_coupons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL COMMENT '用户ID',
  `coupon_id` bigint(20) NOT NULL COMMENT '优惠券ID',
  `status` tinyint(4) NOT NULL COMMENT '状态,0=待使用,1=已使用',
  `item_id` bigint(20) DEFAULT NULL COMMENT '使用商品ID',
  `expire_at` timestamp NOT NULL COMMENT '失效时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `user_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `item_id` bigint(20) unsigned NOT NULL COMMENT '商品ID',
  `earning_end_at` timestamp NOT NULL COMMENT '运营结束时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `serial_number` int(11) NOT NULL COMMENT '产品编号',
  `last_earning_at` timestamp NULL DEFAULT NULL COMMENT '最后收益时间',
  `order_sn` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '订单号',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态,0=未评价,1=已评价',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT '购买份数',
  `stoped_at` timestamp NULL DEFAULT NULL COMMENT '禁用时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户产品';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_readpacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `user_readpacks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `opened_at` datetime DEFAULT NULL COMMENT '打开红包时间',
  `amount` decimal(20,2) DEFAULT NULL COMMENT '现金数量',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户红包';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_realnames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `user_realnames` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户ID',
  `image1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片1',
  `image2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片2',
  `paper_type` tinyint(4) NOT NULL COMMENT '证件类型，1=身份证，2=护照，3=驾照',
  `paper_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '证件号',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '审核状态，0=未审核，1=审核成功，2=审核拒绝',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_withdrawals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `user_withdrawals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `status` tinyint(4) NOT NULL COMMENT '状态,0=未审核,1=审核通过,2=审核失败',
  `bankcard_id` bigint(20) unsigned NOT NULL COMMENT '银行卡ID',
  `amount` decimal(8,2) NOT NULL COMMENT '提现金额',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `withdrawal_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '提现单号',
  `transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '支付渠道交易ID',
  `pay_type` tinyint(3) unsigned NOT NULL COMMENT '提现渠道:1=CSPAY,2=TRC-USDT,3=YTPAY',
  `pay_status` tinyint(3) unsigned NOT NULL COMMENT '提现状态1:已提现;2:未提现',
  `order_status` tinyint(3) unsigned NOT NULL COMMENT '订单状态1:已提现2:未提现3:提现取消',
  `pay_time` timestamp NULL DEFAULT NULL COMMENT '提现成功时间',
  `after_balance` decimal(20,2) DEFAULT NULL COMMENT '提现后余额',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户提现';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_youtube_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `user_youtube_links` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL COMMENT '用户ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '链接',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态，0=未审核，1=审核成功，2=审核失败',
  `sort` int(11) NOT NULL DEFAULT '100' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名称',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '头像',
  `mobile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '手机号',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码',
  `trade_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '交易密码',
  `parent_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '上级CODE',
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '我的CODE',
  `balance` decimal(20,2) DEFAULT '0.00' COMMENT '普通余额,可提现',
  `lv1_superior_id` bigint(20) unsigned DEFAULT NULL COMMENT '上级用户ID',
  `lv2_superior_id` bigint(20) unsigned DEFAULT NULL COMMENT '上上级用户ID',
  `lv3_superior_id` bigint(20) unsigned DEFAULT NULL COMMENT '上上上级用户ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `total_invite` int(11) NOT NULL DEFAULT '0' COMMENT '总邀请人数',
  `received_invite` int(11) NOT NULL DEFAULT '0' COMMENT '已收到奖励的邀请人数',
  `unreceive_invite` int(11) NOT NULL DEFAULT '0' COMMENT '未收到奖励的邀请人数',
  `baned_at` timestamp NULL DEFAULT NULL COMMENT '封禁时间',
  `is_simple_redpack` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开启红包是否为简单模式',
  `is_salesman` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否业务员',
  `salesman_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '业务员码',
  `redpacket_balance` decimal(20,2) unsigned DEFAULT '0.00' COMMENT '红包金',
  `mission_balance` decimal(20,2) DEFAULT '0.00' COMMENT '任务金',
  `freeze_end_at` timestamp NULL DEFAULT NULL,
  `remain_daily_mission` int(11) NOT NULL DEFAULT '0' COMMENT '剩余任务次数',
  `unable_withdrawal_balance` decimal(20,2) DEFAULT '0.00' COMMENT '无法提现余额',
  `item_level_id` bigint(20) DEFAULT NULL COMMENT '限购分类',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邮箱',
  `is_recharged_or_buyed` tinyint(4) DEFAULT '0' COMMENT '是否充值或购买商品(有效用户)',
  `last_login_time` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
  `is_coupon_received` tinyint(1) DEFAULT '0' COMMENT '是否领取优惠券',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile_unique_index` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `websockets_statistics_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `websockets_statistics_entries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `peak_connection_count` int(11) NOT NULL,
  `websocket_message_count` int(11) NOT NULL,
  `api_message_count` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `withdrawals_usdt_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
 SET character_set_client = utf8mb4 ;
CREATE TABLE `withdrawals_usdt_receipts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `withdrawal_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '提现单号',
  `chain` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '链',
  `wallet_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '发款钱包地址',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '凭证截图',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` VALUES (4,'2014_10_12_000000_create_users_table',1);
INSERT INTO `migrations` VALUES (5,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO `migrations` VALUES (6,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` VALUES (7,'2016_01_04_173148_create_admin_tables',2);
INSERT INTO `migrations` VALUES (8,'2020_09_07_090635_create_admin_settings_table',2);
INSERT INTO `migrations` VALUES (9,'2020_09_22_015815_create_admin_extensions_table',2);
INSERT INTO `migrations` VALUES (10,'2020_11_01_083237_update_admin_menu_table',2);
INSERT INTO `migrations` VALUES (11,'2023_03_27_14460000_create_websockets_statistics_entries_table',3);
INSERT INTO `migrations` VALUES (12,'2023_03_28_094730_create_banners_table',4);
INSERT INTO `migrations` VALUES (13,'2023_03_28_102100_create_settings_table',5);
INSERT INTO `migrations` VALUES (14,'2023_03_28_105824_create_public_notices_table',6);
INSERT INTO `migrations` VALUES (15,'2023_03_28_112359_create_items_table',7);
INSERT INTO `migrations` VALUES (16,'2023_03_28_153716_create_money_log_table',8);
INSERT INTO `migrations` VALUES (17,'2023_03_28_163333_create_user_cashback_table',9);
INSERT INTO `migrations` VALUES (18,'2023_03_28_165050_create_sign_log_table',10);
INSERT INTO `migrations` VALUES (19,'2023_03_28_171300_create_user_items_table',11);
INSERT INTO `migrations` VALUES (23,'2023_03_28_172130_create_orders_table',12);
INSERT INTO `migrations` VALUES (24,'2023_03_28_173933_create_order_usdt_table',12);
INSERT INTO `migrations` VALUES (25,'2023_03_29_024837_create_user_withdrawals_table',13);
INSERT INTO `migrations` VALUES (26,'2023_03_29_025148_create_user_bankcard_table',13);
INSERT INTO `migrations` VALUES (27,'2023_03_29_025650_user_item_add_serial_number',14);
INSERT INTO `migrations` VALUES (28,'2023_03_29_083543_users_add_invite_award_columns',15);
INSERT INTO `migrations` VALUES (29,'2023_03_31_031709_user_cashback_add_log_type',16);
INSERT INTO `migrations` VALUES (30,'2023_04_01_013345_user_item_add_last_earning_at_column',17);
INSERT INTO `migrations` VALUES (31,'2023_04_01_054636_user_withdrawl_add_thrid_platform_colmuns',18);
INSERT INTO `migrations` VALUES (32,'2023_04_01_060617_item_add_secondary_image',19);
INSERT INTO `migrations` VALUES (33,'2023_04_01_064306_moneylog_add_user_item_id',20);
INSERT INTO `migrations` VALUES (34,'2023_04_03_024739_user_bankcard_add_email',21);
INSERT INTO `migrations` VALUES (35,'2023_04_07_122601_create_customer_services_table',22);
INSERT INTO `migrations` VALUES (36,'2023_04_11_111552_users_add_baned_at',23);
INSERT INTO `migrations` VALUES (40,'2023_04_12_130926_item_change_for_clickfarm',24);
INSERT INTO `migrations` VALUES (41,'2023_04_12_143901_user_item_change_for_clickfarm',25);
INSERT INTO `migrations` VALUES (42,'2023_04_12_150003_create_review_tmpls_table',26);
INSERT INTO `migrations` VALUES (43,'2023_04_13_073607_create_review_records_table',27);
INSERT INTO `migrations` VALUES (44,'2023_04_13_075602_create_group_purchase_records_table',27);
INSERT INTO `migrations` VALUES (45,'2023_04_13_084613_user_add_column_freeze_balance',28);
INSERT INTO `migrations` VALUES (46,'2023_04_13_123000_group_purchase_add_status_and_expired_at',29);
INSERT INTO `migrations` VALUES (47,'2023_04_14_114808_create_user_readpacks_table',30);
INSERT INTO `migrations` VALUES (48,'2023_04_14_141853_users_add_is_simple_redpack',31);
INSERT INTO `migrations` VALUES (49,'2023_04_19_114746_create_text_contents_table',32);
INSERT INTO `migrations` VALUES (50,'2023_04_21_074432_create_chat_rooms_table',32);
INSERT INTO `migrations` VALUES (51,'2023_04_21_075057_create_chat_members_table',32);
INSERT INTO `migrations` VALUES (52,'2023_04_21_075819_create_chat_records_table',32);
INSERT INTO `migrations` VALUES (53,'2023_04_21_080955_create_chat_redpackets_table',32);
INSERT INTO `migrations` VALUES (54,'2023_04_21_133520_create_user_activite_codes_table',33);
INSERT INTO `migrations` VALUES (55,'2023_04_21_142139_user_redpack_remove_code',34);
INSERT INTO `migrations` VALUES (56,'2023_04_24_081118_item_change_group_purchase_time',35);
INSERT INTO `migrations` VALUES (57,'2023_04_25_135453_users_add_salesman_column',36);
INSERT INTO `migrations` VALUES (58,'2023_04_25_162520_users_add_salesman_code',37);
INSERT INTO `migrations` VALUES (59,'2023_04_26_143654_chat_room_add_avatar_column',38);
INSERT INTO `migrations` VALUES (60,'2023_05_23_095722_review_records_add_image_and_content',39);
INSERT INTO `migrations` VALUES (61,'2023_05_23_114928_create_item_categories_table',40);
INSERT INTO `migrations` VALUES (62,'2023_05_23_171813_money_log_add_balance_type',41);
INSERT INTO `migrations` VALUES (63,'2023_05_24_100232_item_change_logistics_days_to_logistics_hours',42);
INSERT INTO `migrations` VALUES (64,'2023_05_29_111653_table_customer_services_add_salesman_code',43);
INSERT INTO `migrations` VALUES (65,'2023_06_03_115655_user_withdrawals_add_column_after_balance',44);
INSERT INTO `migrations` VALUES (66,'2023_06_14_111842_user_bankcard_add_subbranch',45);
INSERT INTO `migrations` VALUES (67,'2023_06_17_080908_users_add_freeze_end_at',46);
INSERT INTO `migrations` VALUES (68,'2023_06_17_111144_items_add_item_no',47);
INSERT INTO `migrations` VALUES (69,'2023_06_17_120430_users_add_remain_mission',48);
INSERT INTO `migrations` VALUES (70,'2023_06_17_130952_users_add_unable_withdrawal_balance',49);
INSERT INTO `migrations` VALUES (72,'2023_06_25_134700_users_add_item_level',50);
INSERT INTO `migrations` VALUES (73,'2023_06_25_141942_items_add_link',51);
INSERT INTO `migrations` VALUES (74,'2023_06_26_073248_items_add_description',52);
INSERT INTO `migrations` VALUES (75,'2023_06_26_080414_items_add_joined_count_display',53);
INSERT INTO `migrations` VALUES (77,'2023_06_26_092253_user_item_add_amount',54);
INSERT INTO `migrations` VALUES (78,'2023_06_26_130627_items_add_remain_sales_amount',55);
INSERT INTO `migrations` VALUES (79,'2023_06_26_145412_create_item_price_audit_log_table',56);
INSERT INTO `migrations` VALUES (80,'2023_06_27_081913_items_add_location',57);
INSERT INTO `migrations` VALUES (81,'2023_06_27_082708_items_add_characteristic',58);
INSERT INTO `migrations` VALUES (82,'2023_06_27_114648_bankcard_add_wallet_info',59);
INSERT INTO `migrations` VALUES (83,'2023_06_27_120325_create_withdrawals_usdt_receipts_table',60);
INSERT INTO `migrations` VALUES (84,'2023_06_27_151207_set_user_bankcard_info_is_nullable',60);
INSERT INTO `migrations` VALUES (85,'2023_06_28_075401_create_user_realnames_table',60);
INSERT INTO `migrations` VALUES (89,'2023_06_29_112535_user_add_email',61);
INSERT INTO `migrations` VALUES (91,'2023_06_30_122929_text_content_change_content_length',62);
INSERT INTO `migrations` VALUES (92,'2023_06_30_142003_create_news_table',63);
INSERT INTO `migrations` VALUES (93,'2023_07_05_081637_items_add_auto_dec_stock_column',64);
INSERT INTO `migrations` VALUES (94,'2023_07_07_150432_bankcard_add_bank_code',65);
INSERT INTO `migrations` VALUES (95,'2023_07_14_143911_item_add_sort_column',66);
INSERT INTO `migrations` VALUES (96,'2023_07_14_175957_chat_rooms_add_key',66);
INSERT INTO `migrations` VALUES (97,'2023_07_29_140002_create_pay_channels_table',67);
INSERT INTO `migrations` VALUES (98,'2023_07_31_142610_user_item_add_stoped_at',68);
INSERT INTO `migrations` VALUES (99,'2023_08_03_145300_user_add_is_recharged_or_buyed',69);
INSERT INTO `migrations` VALUES (100,'2023_08_03_145301_user_add_last_login_time',69);
INSERT INTO `migrations` VALUES (101,'2023_08_03_154700_customer_service_add_service_type',70);
INSERT INTO `migrations` VALUES (102,'2023_08_08_073814_create_coupons_table',71);
INSERT INTO `migrations` VALUES (103,'2023_08_08_074111_create_user_coupons_table',71);
INSERT INTO `migrations` VALUES (104,'2023_08_10_133332_item_add_is_earning_at_end',72);
INSERT INTO `migrations` VALUES (107,'2023_08_29_065607_user_mobile_column_add_unique',73);
INSERT INTO `migrations` VALUES (108,'2023_09_06_074431_create_user_youtube_links_table',74);
INSERT INTO `migrations` VALUES (109,'2023_09_14_155800_item_add_presale_time',75);
