create table IF NOT EXISTS b_ipol_dpd_terminal (
	ID int not null auto_increment,

	LOCATION_ID int not null default '0',
	
	CODE varchar(255) null,
	NAME varchar(255) null,

	ADDRESS_FULL  varchar(255) null,
	ADDRESS_SHORT varchar(255) null,
	ADDRESS_DESCR text null,

	PARCEL_SHOP_TYPE varchar(255) null,

	SCHEDULE_SELF_PICKUP varchar(255) null,
	SCHEDULE_SELF_DELIVERY varchar(255) null,
	SCHEDULE_PAYMENT_CASH varchar(255) null,
	SCHEDULE_PAYMENT_CASHLESS varchar(255) null,

	IS_LIMITED char(1) not null default 'N',
	LIMIT_MAX_SHIPMENT_WEIGHT double not null default '0',
	LIMIT_MAX_WEIGHT double not null default '0',
	LIMIT_MAX_LENGTH double not null default '0',
	LIMIT_MAX_WIDTH double not null default '0',
	LIMIT_MAX_HEIGHT double not null default '0',
	LIMIT_MAX_VOLUME double not null default '0',
	LIMIT_SUM_DIMENSION double not null default '0',

	LATITUDE double not null default '0',
	LONGITUDE double not null default '0',

	NPP_AMOUNT double not null default '0',
	NPP_AVAILABLE char(1) not null default 'N',

	UPDATE_CHECKED char(1) not null default 'Y',

	primary key (ID)
);