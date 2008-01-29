#
# Table structure for table 'tx_ratings_data'
#
CREATE TABLE tx_ratings_data (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	reference text NOT NULL,
	rating int(11) DEFAULT '0' NOT NULL,
	vote_count int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY reference (reference(16))
);

#
# Table structure for table 'tx_ratings_iplog'
#
CREATE TABLE tx_ratings_iplog (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	reference text NOT NULL,
	ip varchar(255) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY reference (reference(16))
);
