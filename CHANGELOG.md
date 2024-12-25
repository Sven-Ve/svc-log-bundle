# Changelog



## Version 0.1.0
*Thu, 15 Jul 2021 18:21:44 +0000*
- first test version


## Version 0.1.1
*Thu, 15 Jul 2021 19:17:58 +0000*
- first statistics delopment


## Version 0.2.0
*Sat, 17 Jul 2021 16:51:02 +0000*
- first version integrated in sv-video prod


## Version 0.2.1
*Sat, 17 Jul 2021 20:45:07 +0000*
- added creation of monthly statistics


## Version 0.2.2
*Sun, 18 Jul 2021 20:06:25 +0000*
- added pivot data


## Version 0.2.3
*Sun, 18 Jul 2021 20:38:37 +0000*
- added logLevel to pivotMonthly


## Version 0.2.4
*Tue, 20 Jul 2021 20:26:22 +0000*
- added aggregation by country


## Version 0.2.5
*Wed, 21 Jul 2021 08:14:27 +0000*
- fixed error if no log data exists


## Version 0.2.6
*Fri, 23 Jul 2021 20:58:11 +0000*
- go prod in sv-video


## Version 0.3.0
*Sat, 24 Jul 2021 20:43:19 +0000*
- added parameter min_log_level and creation of prod config file


## Version 0.3.1
*Sun, 25 Jul 2021 16:45:28 +0000*
- added getCountriesForChartJS1 for direct yarn chart.js integration


## Version 0.3.2
*Sun, 25 Jul 2021 20:25:22 +0000*
- fixed min_log_level error, added documentation for getCountriesForChartJS1*


## Version 0.3.3
*Sun, 01 Aug 2021 14:53:04 +0000*
- added dataprovider


## Version 0.3.4
*Sun, 01 Aug 2021 15:14:42 +0000*
- added svcl-log-viewer_controller.js to copy during install


## Version 0.4.0
*Sun, 01 Aug 2021 20:21:10 +0000*
- finishing first version of LogDataProvider


## Version 1.0.0
*Tue, 03 Aug 2021 11:31:45 +0000*
- ready to go to prod, added tests, documentation, ...


## Version 1.0.1
*Tue, 03 Aug 2021 20:15:19 +0000*
- describe the data provider


## Version 1.0.2
*Thu, 05 Aug 2021 20:34:40 +0000*
- improve log viewer loading, show counts


## Version 1.1.0
*Fri, 06 Aug 2021 20:45:03 +0000*
- integrate ajax log viewer for third-party-apps


## Version 1.2.0
*Sat, 07 Aug 2021 20:28:34 +0000*
- install stimulus controller via ux-webpack-logic


## Version 1.2.1
*Sun, 15 Aug 2021 21:50:14 +0000*
- fixing typo in viewer.js/createURL


## Version 1.2.2
*Mon, 16 Aug 2021 21:59:15 +0000*
- don't use data provider, if source columns are hided


## Version 1.3.0
*Sat, 21 Aug 2021 22:03:36 +0000*
- added modal dialog for log details


## Version 1.4.0
*Sun, 22 Aug 2021 21:23:05 +0000*
- moved Resources back to /src, added pseudo columns $source*Text


## Version 1.5.0
*Tue, 24 Aug 2021 22:23:33 +0000*
- added color classes to the log result table


## Version 1.6.0
*Wed, 25 Aug 2021 20:20:09 +0000*
- added user information (if allowed) to log


## Version 1.6.1
*Sat, 28 Aug 2021 19:51:09 +0000*
- added check, if SecurityBundle installed when enable_user_saving = true


## Version 1.6.2
*Sat, 23 Oct 2021 20:52:57 +0000*
- fixed wrong rowCount call in executeStatement


## Version 2.0.0
*Thu, 31 Mar 2022 20:46:48 +0000*
- added initial compatibility to stimulus 3


## Version 2.0.1
*Fri, 22 Apr 2022 20:22:53 +0000*
- fix for symfony 5.4


## Version 2.1.0
*Wed, 27 Apr 2022 16:13:01 +0000*
- ready for symfony 6


## Version 2.1.1
*Sat, 30 Apr 2022 14:13:51 +0000*
- don't create prod config file, see documentation, use simplified config.


## Version 3.0.0
*Sat, 30 Apr 2022 20:20:39 +0000*
- runs only with symfony 5.4 and >6 und php8


## Version 3.1.0
*Mon, 02 May 2022 18:34:17 +0000*
- new parameter to allow no-admin view data


## Version 3.1.1
*Mon, 02 May 2022 18:51:53 +0000*
- new parameter to allow no-admin view data (fix for statistic data)


## Version 3.1.2
*Tue, 03 May 2022 20:50:40 +0000*
- format code with php-cs-fixer, ignore wrong phpstan warning


## Version 3.1.3
*Sat, 14 May 2022 13:54:04 +0000*
- php attributes added


## Version 3.2.0
*Sun, 29 May 2022 21:26:51 +0000*
- purge log console command finished


## Version 3.2.1
*Mon, 30 May 2022 08:24:49 +0000*
- improved error handling in batchFillLocation


## Version 3.3.0
*Mon, 30 May 2022 11:39:27 +0000*
- added force to fillLocation (rescan all empty locations)


## Version 3.3.1
*Mon, 30 May 2022 11:44:29 +0000*
- Improved logging in svc_log:fill-location command


## Version 3.3.2
*Mon, 30 May 2022 11:50:21 +0000*
- Improved performance in svc_log:fill-location command


## Version 3.3.3
*Mon, 30 May 2022 14:30:08 +0000*
- Improved logging in svc_log:fill-location command


## Version 3.3.4
*Mon, 30 May 2022 16:06:37 +0000*
- Small improvements


## Version 3.4.0
*Fri, 24 Jun 2022 19:13:21 +0000*
- added daily statistics to pivot


## Version 3.4.1
*Sat, 25 Jun 2022 08:17:28 +0000*
- added total and total5 (last 5 month) to pivot


## Version 3.4.2
*Thu, 30 Jun 2022 20:06:25 +0000*
- moved to the newer stimulus syntax, some smaller fixes


## Version 4.0.0
*Mon, 18 Jul 2022 19:43:07 +0000*
- build with Symfony 6.1 bundle features, runs only with symfony 6.1


## Version 4.0.1
*Tue, 19 Jul 2022 10:52:23 +0000*
- Improved EaLogStatMonthlyCrudController


## Version 4.0.2
*Tue, 19 Jul 2022 14:38:59 +0000*
- Improved EA Controllers


## Version 4.0.3
*Thu, 21 Jul 2022 18:36:37 +0000*
- licence year update


## Version 4.1.0
*Fri, 12 Aug 2022 14:04:36 +0000*
- added function to delete logs in SvcLogRepository


## Version 4.1.1
*Sun, 16 Oct 2022 19:04:45 +0000*
- added some unit tests


## Version 4.2.0
*Fri, 21 Oct 2022 19:21:27 +0000*
- use SvcParam version 5 with enum


## Version 4.3.0
*Thu, 01 Dec 2022 20:45:12 +0000*
- Fix deprecations for symfony 6.2


## Version 5.0.0
*Sat, 16 Dec 2023 15:23:26 +0000*
- ready for symfony 6.4 and 7


## Version 5.0.1
*Sun, 17 Dec 2023 18:35:58 +0000*
- ready for symfony 6.4 and 7 - fixed test errors


## Version 5.0.2
*Sun, 17 Dec 2023 19:02:34 +0000*
- ready for symfony 6.4 and 7 - fixed type declarations


## Version 5.1.0
*Mon, 01 Jan 2024 14:02:20 +0000*
- ready for assetmapper


## Version 5.2.0
*Mon, 29 Jan 2024 20:46:30 +0000*
- possibility to use logger/monolog too


## Version 5.2.1
*Tue, 30 Jan 2024 21:00:12 +0000*
- add browser info to logger logging


## Version 5.3.0
*Sat, 03 Feb 2024 21:24:58 +0000*
- better exception handling, allow doctrine/orm 3


## Version 5.4.0
*Sat, 10 Feb 2024 19:11:39 +0000*
- add new parameter errorText to logging


## Version 5.5.0
*Fri, 08 Mar 2024 20:40:17 +0000*
- runs with doctrine/orm ^3 too


## Version 5.5.1
*Wed, 12 Jun 2024 19:44:07 +0000*
- disable parameter editing for svcLog_lastRunAggrMonthly 


## Version 5.5.2
*Sun, 16 Jun 2024 16:01:52 +0000*
- use twig component for logview modal


## Version 5.6.0
*Sat, 06 Jul 2024 10:22:49 +0000*
- added test, phpstan level 7, fixed all phpstan errors


## Version 6.0.0
*Tue, 23 Jul 2024 08:36:22 +0000*
- switch to jbtronics/settings-bundle (break compotibility!)
- add daily summary


## Version 6.1.0
*Tue, 23 Jul 2024 19:18:53 +0000*
- added controller for viewing (testing) the daily summary

## Version 7.0.0
*Sat, 16 Nov 2024 20:35:55 +0000*
- Added Bot check. Data model changed!


## Version 7.1.0
*Sun, 17 Nov 2024 09:35:52 +0000*
- Added Bot information to daily summary

## Version 7.2.0
*Sun, 17 Nov 2024 15:34:36 +0000*
- moved to matomo/device-detector bundle. better logging info, data model CHANGED

## Version 7.3.0
*Mon, 18 Nov 2024 21:22:03 +0000*
- update to phpstan 2 and phpunit 11


## Version 7.4.0
*Sun, 24 Nov 2024 21:00:00 +0000*
- added optional kernel exception logging (see documention)


## Version 7.5.0
*Tue, 26 Nov 2024 21:20:19 +0000*
- sending 404 errors configurable


## Version 7.5.0
*Wed, 25 Dec 2024 08:29:03 +0000*
- sending 404 errors configurable


## Version 7.6.0
*Wed, 25 Dec 2024 08:29:31 +0000*
- add extra sleep time for 404 errors
