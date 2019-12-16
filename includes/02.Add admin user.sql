--
-- Default admin user
--

INSERT INTO systemUsers(
            userName, 
            userEmail, 
            EVENT_YOUTUBE, 
            EVENT_SCOREKEEP, 
            EVENT_MANAGEMENT, 
            SOFTWARE_EVENT_SWITCHING, 
            SOFTWARE_ASSIST, 
            SOFTWARE_ADMIN, 
            STATS_EVENT, 
            STATS_ALL, 
            VIEW_HIDDEN, 
            VIEW_SETTINGS, 
            VIEW_EMAIL) 
    values ('admin', 'admin@hemascorecard.com', 1,1,1,1,1,1,1,1,1,1,1);