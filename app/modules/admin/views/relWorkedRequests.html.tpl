{literal}
<script src="{/literal}{$path}{literal}/includes/classes/ajax/flex_lang.js"></script>
<script type="text/javascript" src="{/literal}{$path}{literal}/includes/js/admin/relWorkedRequests.js"></script>
{/literal}

<div class="reports">
    <h2>{$smarty.config.pgr_worked_requests}:</h2>
	<div class="clearfix">	
		<form method="post" action="javascript:;" id="formSearch">
			<ul class="lstForm clearfix">
				<li>
					<ul>
						<li class="info">
							<label for="txtSeparator">{$smarty.config.From}:</label>
						</li>
						<li class="field">
							<input name="fromdate" id="fromdate" type="text" maxlength="10" class="w70 mask" data-format="99/99/9999" />
                			<img src="{$path}/app/themes/{$theme}/images/ico_calendario.gif" class="calendar" align="absmiddle" id="f_fromdate" style="cursor: pointer; "/>
						</li>
					</ul>
				</li>
				<li>
					<ul>
						<li class="info">
							<label for="txtSeparator">{$smarty.config.To}:</label>
						</li>
						<li class="field">
							<input name="todate" id="todate" type="text" maxlength="10" class="w70 mask" data-format="99/99/9999"/>
                			<img src="{$path}/app/themes/{$theme}/images/ico_calendario.gif" align="absmiddle" class="calendar" id="f_todate" style="cursor: pointer; "/>
						</li>
					</ul>
				</li>
				
				<li>
					<ul>
						<li class="info">
							<label for="cmbPerson">{$smarty.config.Operator}:</label>
						</li>
						<li class="field">
							<select name="cmbPerson" class="w240" id="cmbPerson">
								<option value=""> {$smarty.config.all} </option>
								{html_options values=$personids output=$personvals}
							</select>
						</li>
					</ul>
				</li>
				<li>
					<ul>
						<li class="info">
							<label for="txtStatus">{$smarty.config.status}:</label>
						</li>
						<li class="field">
							<select name="status" class="w240" id="txtStatus">
								<option value=""> {$smarty.config.Select} </option>
								{html_options values=$statusids output=$statusvals}
							</select>
						</li>
					</ul>
				</li>
			</ul>
			<ul class="lst-btn">
				<li class="last">
					<input type="submit" value="{$smarty.config.Search}" class="btnOrange tp1"/>
				</li>
			</ul>
		</form>
	</div>
	<div class="loader clearfix alignCenter none">
		<img src="{$path}/app/themes/{$theme}/images/loading_laranja.gif"/>         
    </div>
</div>      
<div id="boxRetorno" class="ml10 mb10 none" style="width: 710px;">
    <ul class="lst-si">
    	<li>
    		<a href="javascript:;" class="btn-font-less">Menos</a>
    	</li>
    	<li>
    		<a href="javascript:;" class="btn-font-more">Mais</a>
    	</li>
    	<li>
    		<a href="#modalSalvar" class="btn-save openModal" title="{$smarty.config.Save}">{$smarty.config.Save}</a>
    	</li>
    	<li>
    		<a href="#modalImpressao" class="btn-print openModal print" rel="boxImpressao" title="{$smarty.config.Print}">{$smarty.config.Print}</a>
    	</li>
    </ul>
    <div id="boxImpressao">
        <table class="tbReports clr" cellpadding="0" cellspacing="0">
        	<colgroup>
        		<col width="100">
        		<col width="220">
        		<col width="160">
				<col width="80">
				<col width="150">
        	</colgroup>
        	<thead>
        		<th>{$smarty.config.Code}</th>
        		<th>{$smarty.config.Subject}</th>
        		<th>{$smarty.config.Operator}</th>
        		<th>{$smarty.config.Date}</th>
        		<th>{$smarty.config.status}</th>        		
        	</thead>
        	<tbody>
        		<!-- -->
        	</tbody>
        </table>    
    </div> 
</div>

<!-- MODAIS -->
<div id="modalImpressao" class="window modalPrint" style="width:900px;">
	<a href="javascript:;" onclick="window.print(); return false;" class="btnPrintModal" title="{$smarty.config.Print}"><span>{$smarty.config.Print}</span></a>
    <div class="modalPrintHeader">
    	<img src="{$path}/app/uploads/logos/{$reportslogo}" height="{$reportsheight}px" width="{$reportswidth}px" id="logo" />
		<div class="ttl">
			<h2>{$smarty.config.Person_report}</h2>
			<span class="date"><!-- --></span>
		</div>
		<a href="javascript:;" class="closeModal btnCloseModal" title="{$smarty.config.Close}">{$smarty.config.Close}</a>
	</div>
	<div class="modalPrintContent">
		<!-- -->
	</div>
</div>

<div id="modalSalvar" class="window" style="width:530px;">
    <form action="javascript:;" method="post">
        <div class="modalHeader">
			<h2>{$smarty.config.pgr_worked_requests}</h2>
			<a href="javascript:;" class="closeModal btnCloseModal" title="{$smarty.config.Close}">{$smarty.config.Close}</a>
		</div>
		<div class="modalContent">
			<div class="clearfix">
				<p>{$smarty.config.Choose_format}</p>
				<ul class="lstTypeFile clearfix">
					<li>
						<input type="radio" name="tpFile" id="filePDF" value="PDF" checked="checked" />
						<label for="filePDF">
							<span class="filePDF">{$smarty.config.File_PDF}</span>
						</label>
					</li>
					<li>
						<input type="radio" name="tpFile" id="fileXLS" value="XLS"/>
						<label for="fileXLS">
							<span class="fileXLS">{$smarty.config.File_XLS}</span>
						</label>
					</li>
					<li>
						<input type="radio" name="tpFile" id="fileCSV" value="CSV"/>
						<label for="fileCSV">
							<span class="fileCSV">{$smarty.config.File_CSV}</span>
						</label>
					</li>
				</ul>
				<ul class="lstForm clearfix mt15 none" id="lstSeparator">
					<li>
						<ul>
							<li class="info">
								<label for="txtSeparator">{$smarty.config.Delimiter}:</label>
							</li>
							<li class="field"><input type="text" value="," maxlength="1" name="txtSeparator" id="txtSeparator" style="width: 30px;"/></li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
		<div class="modalFooter">
			<div class="clearfix">
				<ul class="lst-btn">
					<li class="last">
						<input type="submit" value="{$smarty.config.Export}" class="btnOrange tp1"/>
					</li>
				</ul>
			</div>
		</div>
	</form>
</div>
<!-- END MODAIS -->
