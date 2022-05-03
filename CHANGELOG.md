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
