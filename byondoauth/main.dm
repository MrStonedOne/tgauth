/*
	These are simple defaults for your project.
 */

world 
	fps = 10		// 25 frames per second
	icon_size = 32	// 32x32 icon size by default
	view = 6		// show up to 6 tiles outward from center (13x13 view)

/var/entropychain = ""
/var/DBConnection/dbcon = new()

proc/setup_database_connection()
	if(!dbcon)
		dbcon = new()
	if (dbcon.IsConnected())
		dbcon.Disconnect()
	var/user = sqluser
	var/pass = sqlpass
	var/db = sqldb
	var/address = sqladdress
	var/port = sqlport

	dbcon.Connect("dbi:mysql:[db]:[address]:[port]","[user]","[pass]")
	. = dbcon.IsConnected()
	if (!.)
		world.log << "SQL error: " + dbcon.ErrorMsg()

	return .


proc/establish_db_connection()
	return setup_database_connection()


/world/New()
	establish_db_connection()
	if (fexists("entropychain.txt"))
		entropychain = file2text("entropychain.txt")
		
	..()




/client/proc/redirectclient(url, text="redirecting.....")
	switch (connection)
		if ("web")
			var/html = {"
				<html>
				<head></head>
				<body onload="window.top.location.href = '[url]'">
		
				[text] <br> You should be taken there automatically <a target='_top' href='[url]'>Click here if you are not</a>
		
				</body>
				</html>
			"}
			src << browse(html,"window=redirect")
		if ("seeker")
			var/html = {"
				<html>
				<head></head>
		
				[text] <br> You should be taken there automatically <a target='_top' href='[url]'>Click here if you are not</a>
		
				</body>
				</html>
			"}
			src << browse(html,"window=redirect")
			src << link("[url]")
	spawn(35)
		del(src)

/client/proc/client_error(message, url)
	switch(connection)
		if ("web")
			if (url)
				redirectclient(url, message)
			else
				src << browse(message, "window=redirect")
		if ("seeker")
			src << browse(message, "window=redirect")
	src << "[message]"
	
	sleep(10)
	del(src)
	
/client/New(TopicData)
	world.log << "client connection detected. ckey: [ckey] connection type = [connection]"
	
	if (!(connection in list("web", "seeker")))
		src << "Sorry, but only Web Client and Dream Seeker connections are supported"
		return 0
	. = ..()
	src << browse("Linking accounts.", "window=redirect")
	if (!establish_db_connection())
		client_error("Error: unable to connect to database")
		return 0

	if (IsGuestKey(key))
		client_error("Guests are not allowed. Please log in.", "https://secure.byond.com/login.cgi?login=1;noscript=1;url=http%3A%2F%2Fwww.byond.com%2Fplay%2Fbyondoauth.tgstation13.org%3A31337")
		return 0	

	sleep(5)
	src << browse("Linking accounts..", "window=redirect")
	
	var/token = generate_token()
	
	sleep(5)
	src << browse("Linking accounts...", "window=redirect")
	
	var/DBQuery/query_insert = dbcon.NewQuery("INSERT INTO byond_oauth_tokens (`token`, `key`) VALUES ([dbcon.Quote(token)], [dbcon.Quote(key)])")
	var/queryres = query_insert.Execute()
	if (!queryres)
		world.log << "SQL token error!"
		world.log << "Error message: [query_insert.ErrorMsg()]"
		world.log << "Query return value: [queryres]"
		world.log << "Key/Ckey [key]/[ckey]"
		world.log << "Token: [token]"
		client_error("Unknown error #3.")


	//world.log << "SQL error: " + query_insert.ErrorMsg()
	sleep(5)
	src << browse("Linking accounts....", "window=redirect")
	spawn (10)
		src << browse("Linking accounts.....", "window=redirect")
	spawn (20)
		redirectclient("https://tgstation13.org/phpBB/linkbyondaccount.php?token=[url_encode(token)]", "Sending you to the forums to finalize the link.")
	



	
/client/proc/generate_token()
	if (prob(10))
		text2file("entropychain.txt", entropychain)
	
	#define RANDOM_STRING "SHA2(CONCAT(RAND(),UUID(),[dbcon.Quote("[entropychain][GUID()][rand()*rand(999999)][world.time][GUID()][rand()*rand(999999)][world.timeofday][GUID()][rand()*rand(999999)][world.realtime][GUID()][rand()*rand(999999)][time2text(world.timeofday)][GUID()][rand()*rand(999999)][world.tick_usage][computer_id][address][ckey][key][GUID()][rand()*rand(999999)]")],RAND(),UUID()), 512)"
	
	var/DBQuery/query_getset_token = dbcon.NewQuery("SELECT [RANDOM_STRING], [RANDOM_STRING]")
	
	if(!query_getset_token.Execute())
		client_error({"Unknown error #1."})
		return
	
	if(!query_getset_token.NextRow())
		client_error({"Unknown error #2."})
		return
	
	. = query_getset_token.item[1]
	
	entropychain = "[query_getset_token.item[2]]"
	