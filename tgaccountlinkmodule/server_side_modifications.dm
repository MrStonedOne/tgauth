/client/var/forumlinklimit = 0
/client/verb/linkforumaccount()
	set category = "OOC"
	set name = "Link Forum Account"
	set desc = "Validates your byond account to your forum account. Required to post on the forums."
	
	if (forumlinklimit > world.time + 100)
		to_chat(src, {"<span class="userdanger">Please wait 10 game seconds between forums link attempts.</span>"})
		return

	forumlinklimit = world.time

	if (!SSdbcore.Connect())
		to_chat(src, {"<span class="danger">No connection to the database.</span>"})
		return
	
	if  (IsGuestKey(ckey))
		to_chat(src, {"<span class="danger">Guests can not link accounts.</span>"})
	
	var/datum/DBQuery/query_getset_token = SSdbcore.NewQuery("SELECT SHA2(CONCAT(RAND(),UUID(),'[sanitizeSQL("[GUID()][computer_id][address][rand()][ckey][key]")]',RAND(),UUID()), 512)")
	if(!query_getset_token.Execute())
		to_chat(src, {"<span class="danger">Unknown error #1.</span>"})
		qdel(query_getset_token)
		return
	if(!query_getset_token.NextRow())
		to_chat(src, {"<span class="danger">Unknown error #2.</span>"})
		qdel(query_getset_token)
		return
	var/token = query_getset_token.item[1]
	query_getset_token.Close()
	query_getset_token.sql = "INSERT INTO coderbus.byond_oauth_tokens (`token`, `key`) VALUES ('[sanitizeSQL(token)]', '[sanitizeSQL(key)]')"
	if(!query_getset_token.Execute())
		to_chat(src, {"<span class="danger">Unknown error #3.</span>"})
		qdel(query_getset_token)
		return
	
	qdel(query_getset_token)
	to_chat(src, {"Now opening a window to login to your forum account, Your account will automatically be linked the moment you log in. If this window doesn't load, Please go to <a href="https://tgstation13.org/phpBB/linkbyondaccount.php?token=[token]">https://tgstation13.org/phpBB/linkbyondaccount.php?token=[token]</a> This link will expire in 30 minutes."})
	src << link("https://tgstation13.org/phpBB/linkbyondaccount.php?token=[token]")

