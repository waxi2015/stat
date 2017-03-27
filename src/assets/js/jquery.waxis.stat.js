;(function($){

	var plugins = {};

	$.fn.waxisstat = function ( type, parameters ) {
	
		var elem = this;

		var id = this.attr('id');

		var container = $(elem).closest('.wax-stat-chart-container');

		var stat = $(elem).closest('.wax-stat');

		if (plugins[id] === undefined) {
			plugins[id] = {
				type : null,
				data : [],
				chartTitle : null,
				barWidth : null,
				showEvery : null,
				format : null,
				inited : null,
			}
		}

		var init = function () {
			switch (type) {
				case 'chart':
					initChart();
					break;
			}

			container.find('.dropdown-menu a').click(function(e){
				e.preventDefault();

				var source = $(this).attr('href'),
					title = container.find('.chart-title');

				title.text($(this).text());
				title.attr('data-source', source);

				refreshChart();
			});

			container.find('.date-year').change(function(){
				var val = parseInt(container.find('.date-year').val());

				if (typeof val != 'number' || val > 9999 || val < 1000) {
					val = new Date().getFullYear();
				}

				container.find('.date-year').val(val);
				refreshChart();
			});

			container.find('.date-month').change(function(){
				var val = parseInt(container.find('.date-month').val());

				if (typeof val != 'number') {
					val = 1;
				}

				if (val < 1) {
					val = 1;
				}

				if (val > 12) {
					val = 12;
				}

				if (val < 10) {
					val = '0' + val;
				}

				container.find('.date-month').val(val);

				refreshChart();
			});

			container.find('.date-next').click(function(e){
				e.preventDefault();
				
				var year = parseInt(container.find('.date-year').val()),
					month = parseInt(container.find('.date-month').val()),
					nextYear = year,
					nextMonth = month + 1;

				if (nextMonth > 12) {
					nextMonth = 1;
					nextYear = year + 1;
				}

				if (nextMonth < 10) {
					nextMonth = '0' + nextMonth;
				}

				container.find('.date-year').val(nextYear);
				container.find('.date-month').val(nextMonth);

				refreshChart();
			});

			container.find('.date-back').click(function(e){
				e.preventDefault();
				
				var year = parseInt(container.find('.date-year').val()),
					month = parseInt(container.find('.date-month').val()),
					prevYear = year,
					prevMonth = month - 1;

				if (prevMonth < 1) {
					prevMonth = 12;
					prevYear = year - 1;
				}

				if (prevMonth < 10) {
					prevMonth = '0' + prevMonth;
				}

				container.find('.date-year').val(prevYear);
				container.find('.date-month').val(prevMonth);

				refreshChart();
			});

			container.find('.period-selector').click(function(e){
				e.preventDefault();
				
				container.find('.period-selector').removeClass('active');
				$(this).addClass('active');

				if ($(this).attr('href') == 'year') {
					container.find('.date-back,.date-next,.date-year,.date-month').hide();
				} else {
					container.find('.date-back,.date-next,.date-year,.date-month').show();
				}

				refreshChart();
			});

			plugins[id].inited = true;
		}

		var refreshChart = function () {
			$.post('/wax/stat/chart', {
				id: id,
				source: container.find('.chart-title').attr('data-source'),
				year: container.find('.date-year').val(),
				month: container.find('.date-month').val(),
				period: container.find('.period-selector.active').attr('href'),
				_token: stat.find('[name="_token"]').val(),
				descriptor: stat.find('[name="statDescriptor"]').val(),
			}, function (response) {
				plugins[id].data = response.data;
				plugins[id].chartTitle = response.chartTitle;
				plugins[id].barWidth = response.barWidth;
				plugins[id].showEvery = response.showEvery;
				plugins[id].format = response.format;
				initChart();
			})
		}

		var initChart = function () {
			if (plugins[id].inited === true) {
				drawChart()
			} else {
				google.charts.load('current', {'packages':['corechart']});
				google.charts.setOnLoadCallback(drawChart);
			}

			$(window).resize(function(){
				if(this.resizeTO) {
					clearTimeout(this.resizeTO);
				}

				this.resizeTO = setTimeout(function() {
					$('#' + id).find('> div').remove();
					drawChart();
				}, 500);
			})

			function drawChart() {
				var data = google.visualization.arrayToDataTable(plugins[id].data);
				var options = {
					hAxis: {showTextEvery: plugins[id].showEvery},
					vAxes: {
						0: {
							gridlines: {
								color: 'transparent',
								count: 3
							},
							textPosition:'in',
							format: '0',
							minValue:0,
			                viewWindow: {
			                    min: 0
			                },
							format: plugins[id].format
						},
				      	1: {
							gridlines: {
								color: '#f1f1f1',
								count: 3
							},
							textPosition:'in',
							format: '0',
							minValue:0,
			                viewWindow: {
			                    min: 0
			                },
							format: plugins[id].format
				      	}
					},
					series: {
						0: {
							targetAxisIndex:0,
						},
						1: {
							targetAxisIndex:1,
							type: 'bars'
						},
					},
					colors:['#50C1D7','#3dbbd2'],
					chartArea:{
						left:50,
						right:50,
						top:100,
						height:200,
					},
					bar: {
						groupWidth: plugins[id].barWidth
					},
					title: plugins[id].chartTitle,
					titleTextStyle: {
				      color: '676a6c',
				      fontSize: 18,
				      bold: false
				    },
					focusTarget:'category',
					areaOpacity:0.1,
					lineWidth:3,
					pointSize:4,
					fontSize:11,
					legend : {
						position: 'top',
						alignment: 'end',
						textStyle: {
							fontSize: 14
						}
					},
					height: 350
				};

		        var chart = new google.visualization.AreaChart(document.getElementById(id.replace('#', '')));
		        chart.draw(data, options);
	      	}
		}

		plugins[id].data = parameters.data;
		plugins[id].chartTitle = parameters.chartTitle;
		plugins[id].barWidth = parameters.barWidth;
		plugins[id].showEvery = parameters.showEvery;
		plugins[id].format = parameters.format;

		init();

		return this;
	}

	
}(jQuery))