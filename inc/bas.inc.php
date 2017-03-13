		</div>
		<footer>
			
			<div class="container">
				<p class="text-center">
					LOKISALLE <br>
					300 boulevard de Vaugirard <br>
					75015 Paris France
				</p>
			</div>

			<div class="row marginMoins">
				<div class="col-sm-12 paddingPlus">
					
					<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2626.0496408621375!2d2.295626615712275!3d48.83819181008279!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e670155e708f7b%3A0xc1375b95f3fddee5!2s300+Rue+de+Vaugirard%2C+75015+Paris!5e0!3m2!1sfr!2sfr!4v1487837086606" width="600" height="450" style="border:0" allowfullscreen></iframe>

				</div>
			</div>
			<div class="container">
				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 text-center infos-bas">
						<a href="<?php echo RACINE_SITE; ?>mentions_legales.php">Mentions légales</a> - 
						<a href="<?php echo RACINE_SITE; ?>cgv.php">Conditions générales de vente</a> - 
						<?php echo date('Y');?> - Tous droits réservés - <a href="https://fr.linkedin.com/in/alexandra-bogdanova-415a85122">Alexandra Bogdanova</a>
					</div>
				</div>
			</div>
		</footer>
		<script type="text/javascript" src="<?php echo RACINE_SITE; ?>inc/js/jquery-3.1.1.min.js"></script>
		<script type="text/javascript" src="<?php echo RACINE_SITE; ?>inc/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="<?php echo RACINE_SITE; ?>inc/bootstrap-3.3.7-dist/js/sb-admin-2.min.js"></script>
		<script type="text/javascript" src="<?php echo RACINE_SITE; ?>inc/jquery-ui-1.12.1-3.custom/jquery-ui.min.js"></script>
		<script type="text/javascript" src="<?php echo RACINE_SITE; ?>inc/jQuery-Timepicker-Addon-master/dist/jquery-ui-timepicker-addon.js"></script>
		<script type="text/javascript" src="<?php echo RACINE_SITE; ?>inc/jQuery-Timepicker-Addon-master/dist/i18n/jquery-ui-timepicker-fr.js"></script>
		<script type="text/javascript" src="<?php echo RACINE_SITE; ?>inc/js/source/jquery.fancybox.js"></script>
		<script type="text/javascript" src="<?php echo RACINE_SITE; ?>inc/js/charts/vendor/raphael/raphael.min.js"></script>
		<script type="text/javascript" src="<?php echo RACINE_SITE; ?>inc/js/charts/vendor/morrisjs/morris.min.js"></script>
		<script type="text/javascript" src="<?php echo RACINE_SITE; ?>inc/js/charts/vendor/flot/jquery.flot.js"></script>
		<script type="text/javascript" src="<?php echo RACINE_SITE; ?>inc/js/charts/vendor/flot/jquery.flot.pie.js"></script>
		<script type="text/javascript" src="<?php echo RACINE_SITE; ?>inc/js/charts/vendor/flot/jquery.flot.resize.js"></script>
		<script type="text/javascript" src="<?php echo RACINE_SITE; ?>inc/js/charts/vendor/flot/jquery.flot.time.js"></script>
		<script type="text/javascript" src="<?php echo RACINE_SITE; ?>inc/js/charts/vendor/flot-tooltip/jquery.flot.tooltip.min.js"></script>



		<script type="text/javascript">

			$(document).ready(function(){
				// Affichage d'erreur d'inscription dans le modal avec AJAX
				$('#inscription2').submit(function(e){
					e.preventDefault();
					$.ajax({
						type: 'POST',
						url: '<?php echo RACINE_SITE?>inscription.php',
						data: $('form#inscription2').serialize(),
						success: function(data){
							$('#erreur_inscription').html('<br><p class="alert alert-danger">' + data + '</p>');
							if(data == "")
							{
								document.location.href=$('#inscription2').attr('data-redirection');
							}
						} 
					});
				});

				// 

				// affichage d'erreur de connexion dans le modal
				$('#connexion1').submit(function(e){
					e.preventDefault();
					$.ajax({
						datatype: 'html',
						type: 'POST',
						url: '<?php echo RACINE_SITE?>connexion.php',
						data: $('form#connexion1').serialize(),
						success: function(data){
							$('#erreur_connexion').html('<br><p class="alert alert-danger">' + data + '</p>');
							if(data == "")
							{
								document.location.href=$('#connexion1').attr('data-redirection');
							}
						}
					});
				});

				// DATEPICKER
				// jour d'aujourd'hui objet date
				var dateToday = new Date();
				// options de tous les datepickers
				var descDate = {
				  dateFormat: 'dd/mm/yy', 
				  monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'], 
				  dayNamesMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
				  firtsDay:1,
				  showAnim: 'slideDown'
				};

				// Appliquer des paramètres par défaut à tous les datepickers
				$.datepicker.setDefaults(descDate);

				// options particulières pour la date d'arrivée et appel datepicker arrivée
				var dateA;
				dateA = $('#date_arrivee').datepicker({
				  minDate: dateToday,
				  firstDay:1
				})/*<?php if(!isset($resultat_produit['date_arrivee_fr']) || empty($resultat_produit['date_arrivee_fr'])){ echo '.datepicker(\'setDate\', dateToday)'; }?>*/.on('change', function(){
				  // au choix de la date d'arrivée changer la date de départ
				  var dateArriveeMili = Date.parse($('#date_arrivee').datepicker('getDate'));
				  var dateDepartMinMili = dateArriveeMili + 86400000; // ajouter 1 jour
				  var dateDepartMin = new Date(dateDepartMinMili); // convertir en objet date départ
				  dateD.datepicker('option', 'minDate', dateDepartMin);
				});
				
				// options particulières pour la date de départ et appel datepicker départ
				var dateD;
				dateD = $('#date_depart').datepicker({
				  minDate: +1,
				  firstDay:1
				});

				// TIMEPICKER
				$('#heure_arrivee').timepicker({
					hour: 9,
					minute: 0,
					defaultValue: '09:00'
				});
				$('#heure_depart').timepicker({
					hour: 19,
					minute: 0,
					defaultValue: '19:00'
				});

				// TRI AJAX INDEX
			    // récupérer le résultat de la requête mysql pour trier les produits
			    $('#tri').change(function(){
			        $.ajax({
			            url: $('#tri').attr('action'),
			            type: 'POST', 
			            dataType: 'html',
			            data: $('#tri').serialize(),
			            success: function(data){
			                $('#fiches').html(data);
			            },
			            error: function(alert){
			                console.log('erreur');
			            }
			        });
			    });

			    // LIGHTBOX
			    $('.fancybox').fancybox();
			});

			// STATISTIQUES : SALLES MEILLEURE NOTE

			var salle1 = $("#salle0").attr('data-content');
			var salle2 = $("#salle1").attr('data-content');
			var salle3 = $("#salle2").attr('data-content');
			var salle4 = $("#salle3").attr('data-content');
			var salle5 = $("#salle4").attr('data-content');

			var note1 = parseFloat($("#note0").attr('data-content'));
			var note2 = parseFloat($("#note1").attr('data-content'));
			var note3 = parseFloat($("#note2").attr('data-content'));
			var note4 = parseFloat($("#note3").attr('data-content'));
			var note5 = parseFloat($("#note4").attr('data-content'));

			new Morris.Line({
			  // ID of the element in which to draw the chart.
			  element: 'noteChart',
			  // Chart data records -- each entry in this array corresponds to a point on
			  // the chart.
			  data: [
			    { salle: salle1, note: note1 },
			    { salle: salle2, note: note2 },
			    { salle: salle3, note: note3 },
			    { salle: salle4, note: note4 },
			    { salle: salle5, note: note5 }
			  ],
			  // The name of the data record attribute that contains x-values.
			  xkey: 'salle',
			  // A list of names of data record attributes that contain y-values.
			  ykeys: ['note'],
			  // Labels for the ykeys -- will be displayed when you hover over the
			  // chart.
			  parseTime: false,
			  resize: true,
			  pointSize: 6,
			  lineWidth: 2,
			  ymax: 'auto',
			  ymin: 1,
			  axes: true,
			  smooth: false,
			  labels: ['Note'],

			});
			
			
			// STATISTIQUES SALLES LES PLUS COMMANDEES

			var salleC1 = $("#salleC0").attr('data-content');
			var salleC2 = $("#salleC1").attr('data-content');
			var salleC3 = $("#salleC2").attr('data-content');
			var salleC4 = $("#salleC3").attr('data-content');
			var salleC5 = $("#salleC4").attr('data-content');

			var comm1 = parseFloat($("#nbr0").attr('data-content'));
			var comm2 = parseFloat($("#nbr1").attr('data-content'));
			var comm3 = parseFloat($("#nbr2").attr('data-content'));
			var comm4 = parseFloat($("#nbr3").attr('data-content'));
			var comm5 = parseFloat($("#nbr4").attr('data-content'));

			new Morris.Bar({
			  // ID of the element in which to draw the chart.
			  element: 'salleCommandeChart',
			  // Chart data records -- each entry in this array corresponds to a point on
			  // the chart.
			  data: [
			    { salle: salleC1, note: comm1 },
			    { salle: salleC2, note: comm2 },
			    { salle: salleC3, note: comm3 },
			    { salle: salleC4, note: comm4 },
			    { salle: salleC5, note: comm5 }
			  ],
			  // The name of the data record attribute that contains x-values.
			  xkey: 'salle',
			  // A list of names of data record attributes that contain y-values.
			  ykeys: ['note'],
			  // Labels for the ykeys -- will be displayed when you hover over the
			  // chart.
			  parseTime: false,
			  resize: true,
			  pointSize: 6,
			  lineWidth: 2,
			  ymax: 'auto',
			  ymin: 0,
			  axes: true,
			  smooth: false,
			  labels: ['Nombre commandes'],

			});


			// STATISTIQUES MEMBRES QUI COMMANDENT LE PLUS (QUANTITE)

			var membreC1 = $("#membreC0").attr('data-content');
			var membreC2 = $("#membreC1").attr('data-content');
			var membreC3 = $("#membreC2").attr('data-content');
			var membreC4 = $("#membreC3").attr('data-content');
			var membreC5 = $("#membreC4").attr('data-content');

			var comm1 = parseFloat($("#nbrCom0").attr('data-content'));
			var comm2 = parseFloat($("#nbrCom1").attr('data-content'));
			var comm3 = parseFloat($("#nbrCom2").attr('data-content'));
			var comm4 = parseFloat($("#nbrCom3").attr('data-content'));
			var comm5 = parseFloat($("#nbrCom4").attr('data-content'));

			new Morris.Donut({
			  // ID of the element in which to draw the chart.
			  element: 'membreQuantChart',
			  // Chart data records -- each entry in this array corresponds to a point on
			  // the chart.
			  data: [
			    { label: membreC1, value: comm1 },
			    { label: membreC2, value: comm2 },
			    { label: membreC3, value: comm3 },
			    { label: membreC4, value: comm4 },
			    { label: membreC5, value: comm5 }
			  ],
			  resize: true
			});


			// STATISTIQUES MEMBRES QUI ACHETENT LE PLUS (PRIX)

			var membreP1 = $("#membreP0").attr('data-content');
			var membreP2 = $("#membreP1").attr('data-content');
			var membreP3 = $("#membreP2").attr('data-content');
			var membreP4 = $("#membreP3").attr('data-content');
			var membreP5 = $("#membreP4").attr('data-content');

			var prix1 = parseFloat($("#prix0").attr('data-content'));
			var prix2 = parseFloat($("#prix1").attr('data-content'));
			var prix3 = parseFloat($("#prix2").attr('data-content'));
			var prix4 = parseFloat($("#prix3").attr('data-content'));
			var prix5 = parseFloat($("#prix4").attr('data-content'));

			$(function() {

			    var data = [{
			        label: membreP1,
			        data: prix1
			    }, {
			        label: membreP2,
			        data: prix2
			    }, {
			        label: membreP3,
			        data: prix3
			    }, {
			        label: membreP4,
			        data: prix4
			    }, {
			        label: membreP5,
			        data: prix5
			    }];

			    var plotObj = $.plot($("#membrePrixChart"), data, {
			        series: {
			            pie: {
			                show: true
			            }
			        },
			        grid: {
			            hoverable: true
			        }/*,
			        tooltip: true,
			        tooltipOpts: {
			            content: "%p.0%, %s", // show percentages, rounding to 2 decimal places
			            shifts: {
			                x: 20,
			                y: 0
			            },
			            defaultTheme: false
			        }*/
			    });

			});

		</script>
		
	</body>
</html>