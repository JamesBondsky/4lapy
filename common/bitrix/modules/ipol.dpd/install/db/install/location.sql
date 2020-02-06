create table IF NOT EXISTS b_ipol_dpd_location (
	ID int not null auto_increment,
	
	COUNTRY_CODE varchar(255) null,
	COUNTRY_NAME varchar(255) null,
	
	REGION_CODE varchar(255) null,
	REGION_NAME varchar(255) null,
	
	CITY_ID bigint UNSIGNED NOT NULL default '0',
	CITY_CODE varchar(255) null,
	CITY_NAME varchar(255) null,
	
	LOCATION_ID int not null default '0',

	IS_CASH_PAY char(1) not null default 'N',

	primary key (ID)
);