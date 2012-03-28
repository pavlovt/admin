/** MyToolbar plugin for dataTables
 * - Adds buttons for filtering and to set column visibility
 * - Adds display for current filters
 * - Ability to easily add more buttons
 * 
 * @author	Jennifer Wystup
 */
/***MODIFICATIONS*************************************************
 *  DATE     PROGRAMMER  DESCRIPTION                             *
 *  11/10/10    JLW      Created & Wrote Internal Documentation  *
 *****************************************************************/

//defaults & global variables

/** Changes:
 * 12/7/10 - fixed select box, make it scroll to the top when selected
 *  so that the whole thing shows.
 *  - added colors & descriptions to dropdowns (dropdown.html)
 * 
 */

var MyToolbarInit = {
	"oButtons" : { /** Button options */
		"back" : {
			"show"  : false,
			"title" : "Back one Page",
			"click" : false},
		"filter" : {
			"show" : true,
			"title" : "Filter Data",
			"click" : false},
		"showHide" : {
			"show" : true,
			"title" : "Show/Hide Columns",
			"click" : false}
	},
	"MyToolbarFilterEquals" : "button",
	"MyToolbarFilterOptions" : [
		{text: "includes",			start: "",			end: ""}, 
		{text: "equals",			start: "^",			end: "$"}, 
		{text: "does not include",	start: "^((?!", 	end: ").|\s)*$"}, 
		{text: "not equal to",		start: "^((?!^(^",	end: "$)).|\s)*$"},
		{text: "starts with",		start: "^(",		end: ")(.|\s)*"}, 
		{text: "does not start with",start: "^((?!^(",	end: "))(.|\s)*)$"},
		{text: "ends with",			start: "(.|\s)*(",	end: "$)"}, 
		{text: "does not end with",	start: "(?!.*(",	end: ")$)(^(.|\s)*)"}],	
	"MyToolbarLocation" : "",
	/** Set descriptions for dropdown boxes - see dropdown.html */
	"MyToolbarDescriptions" : [],
	/** Globals for internal use, don't change these */
	"oMyTable" : null,
	"oMyTableStartCols" : [],
	"oMyTableFilterSelects" : [],
	"oMyTableFilterSel" : [],
	"oMyTableFilterIncl" : []
};

//get the current script location
var script = MyToolbar_getActiveScript().src;
script = script.substr(0, script.indexOf("MyToolbar") + 9);
MyToolbarInit.MyToolbarLocation = script;



//Initialize toolbar
(function($) {
	
	/** Filter columns function
	 *  Displays the filtering dialog and handles changes to the filters
	 */
	MyToolbarInit.oButtons.filter.click = function () {
		var table = $(this).parents(".dataTables_wrapper")
			.find(".dataTables_scrollBody table");
		if (table.length <= 0)
			table = $(this).parents(".dataTables_wrapper").find("table.display");
		var id = $(table).attr("id");
		var tbl = $(table).dataTable({"bRetrieve" : true});	
		var cols = tbl.fnSettings().aoColumns;
		var html = "<table class=MyToolbarPopupTable>" +
			"<thead><tr class='ui-state-default'><th>Title</th>" +
			"<th></th><th>Search</th></tr></thead><tbody>";
		var chg = function () {
			var i = parseInt($(this).attr("name"), 10),
				tbl = MyToolbarInit.oMyTable;
			if (!tbl) return false;
			MyToolbarInit.updateFilter(tbl, $(this).val(), i);
		};
		var chg2 = function () {
			var i = parseInt($(this).parents("div.flexBox").attr("id").substr(4), 10);
			var tbl = MyToolbarInit.oMyTable;
			if (!tbl) return false;
			MyToolbarInit.updateFilter(tbl, $(this).val(), i);
		};
		var fnCreateSelect = function (aData, x, selected, tblid) {
			var multiple = false,
				cTitle = cols[x].sTitle,
				desc = MyToolbarInit.MyToolbarDescriptions[id];
			if (tblid && tblid != null) {
				var sel = [], s = selected;
				sel.push({"id": "", "name": "Select All", "color": ""});
				$(aData).each(function () {
					var text1 = this.replace("&amp;", "&"), text; 
					if (desc && desc[cTitle] && desc[cTitle][text1].desc)
						text = desc[cTitle][text1].desc;
					else
						text = text1;
					var color = "";
					if (desc && desc[cTitle] && desc[cTitle][text1].color)
						color = desc[cTitle][text1].color;
					sel.push({"id": this, "name": text, "color": color});
					
				});
				MyToolbarInit.oMyTableFilterSelects[tblid][x] = sel;
				if (s == "") s = "Select All";
				MyToolbarInit.oMyTableFilterSel[tblid][x] = s;
			} else {
				if (selected.indexOf("|") >= 0) {
					multiple = true;
					selected = selected.split("|");
				} else if (selected.indexOf(" or ") >= 0) {
					multiple = true;
					selected = selected.split(" or ");
				}
				var ret = "<option value=''>Select All</option>";
				$(aData).each(function () {
					var text1 = this.replace("&amp;", "&"), text;
					if (desc && desc[cTitle] && desc[cTitle][text1].desc)
						text = desc[cTitle][text1].desc;
					else
						text = text1;
					ret += "<option";
					if (desc && desc[cTitle] && desc[cTitle][text1].color)
						ret += " style='background-color: " + 
							desc[cTitle][text1].color + "'";
					ret += " value='" + this;
					if (multiple && $.inArray($.trim(this), selected) >= 0) {
						ret += "' selected>";
					} else if (!multiple && $.trim(this) == selected) {
						ret += "' selected>";
					} else {
						ret += "'>";
					}
					ret += text + "</option>";
				});
				
				return ret;
			}
		};
		MyToolbarInit.oMyTable = tbl;
		MyToolbarInit.oMyTableFilterSelects[id] = [];
		MyToolbarInit.oMyTableFilterSel[id] = [];
		if (typeof MyToolbarInit.oMyTableFilterIncl[id] == 'undefined')
			MyToolbarInit.oMyTableFilterIncl[id] = [];
		for (i = 0; i < cols.length; i++) {
			var value = tbl.fnSettings().aoPreSearchCols[i].sSearch;
			value = MyToolbarInit.fnStripRegex(value, "", i);
			
			var incl = typeof MyToolbarInit.oMyTableFilterIncl[id][i] == 'undefined' ?
					"includes" : MyToolbarInit.oMyTableFilterIncl[id][i];
			html += "<tr class='" + (i % 2 == 0 ? "even" : "odd") + "'>" +
					"<td>" + cols[i].sTitle + "</td><td>";
			if (MyToolbarInit.MyToolbarFilterEquals == "dropdown") {
				html += "<select id='eq" + i + "'>";
				var opt = MyToolbarInit.MyToolbarFilterOptions;
				for (var j = 0; j < opt.length; j++) {
					html += "<option value='" + opt[j].text + 
						(incl == opt[j].text ? "' selected>" : "'>") + 
						opt[j].text + "</option>"; 
				}
				html += "</select>";
			} else {
				html += "<button id='eq" + i + "'>" + incl + "</button>";
			}
			html += "</td><td>";

			if (cols[i].sClass == "multDropdown") {
				var cdata = tbl.fnGetColumnData(i);
				html += "<select multiple" + (cdata.length > 10 ? " size='10'" : "") + 
					" name='" + i + "'>" +
					fnCreateSelect(cdata, i, value) + "</select>";
			} else if (cols[i].sClass == "dropdown" && $("<div />").flexbox) {
				fnCreateSelect(tbl.fnGetColumnData(i), i, value, id);
				html += "<div class=flexBox id='flex" + i + "'></div>";
			} else if (cols[i].sClass == "dropdown") {
				html += "<select name='" + i + "'>" +
					fnCreateSelect(tbl.fnGetColumnData(i), i, value) + 
					"</select>";
			} else if (cols[i].sClass == "datepicker" || cols[i].sType == "date") {
				html += "<input name='" + i + "' value='" + value +
					"' style='width: 206px' class='datePick'>";
			} else if (cols[i].sClass != "none") {
				html += "<input name='" + i + "' value='" + value + 
					"' style='width: 206px'>";
			}
			html += "</td></tr>";
		}
		html += "</tbody></table>";
		$("#MyToolbar_filter").dialog("close");
		$("#MyToolbar_showHide").dialog("close");
		$("<div />").html(html).attr("id", "MyToolbar_filter")
			.find("input").keyup(chg).end() //.change(chg).end()
			.find("select:not([id^='eq'])").change(chg).end()
			.dialog({
				title: "Filter Data",
				width: $(window).width() < 600 ? $(window).width() - 50 : 600, 
				height: $(window).height() < 400 ? $(window).height() - 50 : 400,
				close: function () {
					var tbl = $(MyToolbarInit.oMyTable.fnSettings().nTable).attr("id");
					var incl = [];
					$("#MyToolbar_filter [id^='eq']").each(function (i) {
						var t = $(this).val();
						if (typeof t == 'undefined' || t == '') 
							t = $(this).text();
						incl[i] = t;
					});
					MyToolbarInit.oMyTableFilterIncl[tbl] = incl;				
					MyToolbarInit.oMyTable = null;
					$("#MyToolbar_filter").remove();
				},
				buttons: {
					"Clear Filters" : function () {
						var tbl = MyToolbarInit.oMyTable;
						var len = tbl.fnSettings().aoColumns.length;
						$(this).parents("div.ui-dialog")
							.find("td>input").val("").end()
							.find("td>select").each(function () {
								$(this).val("");
							}).end()
							.find(".flexBox").each(function () {
								var id = "#" + $(this).attr("id");
								$(id + "_hidden").val("");
								$(id + "_input").val("Select All");
							}).end()
							.find("[id^='eq']").button("option", "label", "includes");
						for (var i = 0; i < len; i++) {
							if (tbl.fnSettings().aoPreSearchCols[i].sSearch != "")
								tbl.fnFilter("", i, false, true);
						}
						MyToolbarInit.FilterDisplayUpdate(tbl);
					},
					"Close" : function () {
						$("#MyToolbar_filter").dialog("close");
					}
				}
			})
			.parents(".ui-dialog:first").addClass("MyToolbarPopup").end()
			.find(".datePick").datepicker({
				dateFormat: "mm/dd/y"
			}).end()
			.find(".flexBox").each(function (i) {
				var fid = parseInt($(this).attr("id").substr(4), 10);
				$(this).flexbox({
					"results" : MyToolbarInit.oMyTableFilterSelects[id][fid], 
					"total" : MyToolbarInit.oMyTableFilterSelects[id][fid].length
				}, {
					initialValue: MyToolbarInit.oMyTableFilterSel[id][fid], 
					onSelect: function () {
						var i = parseInt($(this).parents("div.flexBox").attr("id").substr(4), 10);
						var tbl = MyToolbarInit.oMyTable;
						if (!tbl) return false;
						MyToolbarInit.updateFilter(tbl, $(this).attr("hiddenValue"), i);
					},
					filterResults: false,
					resultTemplate: '<div style="background-color: {color}">{name}</div>' 
				}).find(".ffb-arrow").mouseup(function () {
					var check = $(this).parents(".flexBox").find(".ffb");
					if ($(check).length > 0 && $(check).css("display") == "none") {
						var pos = $(this).parents(".flexBox").position();
						var cn  = $(this).parents(".ui-dialog-content");
						$(cn).scrollTop($(cn).scrollTop() + pos.top);
					}
				});
			}).end()
			.find(".flexBox input").keyup(chg2).end() //.change(chg2).end()
			.find("button").button().css("font-size", "8pt")
			.click(function () {
				//change button text
				var options = MyToolbarInit.MyToolbarFilterOptions, i = 0;
				for (; i < options.length; i++) {
					if ($(this).text() == options[i].text)
						break;
				}
				i++;
				//var i = $.inArray($(this).text(), options) + 1;
				if (i >= options.length || i < 0) i = 0;
				$(this).button("option", "label", options[i].text);
				//update filter
				var val = $(this).parents("tr").find("input").val();
				if (typeof val == 'undefined')
					val = $(this).parents("tr").find("select").val();
				val = val == "Select All" ? "" : val;
				MyToolbarInit.updateFilter(MyToolbarInit.oMyTable, val, 
					parseInt($(this).attr("id").substr(2), 10));
			}).end()
			.find("select[id^='eq']").change(function () {
				var val = $(this).parents("tr").find("input").val();
				if (typeof val == 'undefined')
					val = $(this).parents("tr").find("select:last").val();
				val = val == "Select All" ? "" : val;
				MyToolbarInit.updateFilter(MyToolbarInit.oMyTable, val,
					parseInt($(this).attr("id").substr(2), 10));
			});
	};
	/** Updates the filters using the equals buttons (includes/equals/etc).
	 *  Creates the proper regex for filtering and then updates
	 *  the filter on the table. 
	 */
	MyToolbarInit.updateFilter = function (table, value, column) {
		if (typeof value == "undefined" || value == null)
			value = "";
		var regex = value, reg = false, smart = true;
		var id = $(table.fnSettings().nTable).attr("id");
		var btn = $("#MyToolbar_filter #eq" + column).val();
		if (typeof btn == 'undefined' || btn == '')
			btn = $("#MyToolbar_filter #eq" + column).text();
		if (btn != "includes") {
			reg = true;
			smart = false;
		}
		if (typeof value == "object") { //for multiple selects
			var v = "";
			for (var i = 0; i < value.length; i++) {
				v += value[i] + (i < value.length - 1 ? "|" : "");
			}
			value = v;
			regex = value;
			reg = true;
			smart = false;
		}
		var opt = MyToolbarInit.MyToolbarFilterOptions;
		for (var i = 0; i < opt.length; i++) {
			if (btn == opt[i].text) {
				regex = opt[i].start + value + opt[i].end;
				break;
			}
		}
		MyToolbarInit.oMyTableFilterIncl[id][column] = btn;
		table.fnFilter(regex, column, reg, smart);
		MyToolbarInit.FilterDisplayUpdate(table);
	};
	
	/** ShowHide function
	 *  Displays the dialog to show and hide specific columns
	 */
	MyToolbarInit.oButtons.showHide.click = function () {
		var table = $(this).parents(".dataTables_wrapper")
			.find(".dataTables_scrollBody table");
		if (table.length <= 0)
			table = $(this).parents(".dataTables_wrapper").find("table.display");
		var id = $(table).attr("id");
		table = $(table).dataTable({"bRetrieve" : true});
		var cols = table.fnSettings().aoColumns, 
			sc = [], _that = this;
		//table of column names
		var html = "<table class=MyToolbarPopupTable>" +
				"<thead><tr class='ui-state-default'><th>Show</th>" +
				"<th>Title</th></tr></thead><tbody>";
		$(cols).each(function (i) {
			html += "<tr class='" + (i % 2 == 0 ? "even" : "odd") + "'>" +
					"<td><input type=checkbox name='" + i + 
					(this.bVisible ? "' checked" : "'") + "></td>" +
					"<td>" + this.sTitle + "</td></tr>";
			sc[i] = this.bVisible;
		});
		html += "</tbody></table>";
		if (typeof MyToolbarInit.oMyTableStartCols[id] == 'undefined') {
			MyToolbarInit.oMyTableStartCols[id] = sc;
		}
		$("#MyToolbar_filter").dialog("close");
		$("#MyToolbar_showHide").dialog("close");
		MyToolbarInit.oMyTable = table;
		$("<div />").attr("id", "MyToolbar_showHide").html(html)
			.find("table input:checkbox").click(function () {
				var tbl = MyToolbarInit.oMyTable;
				tbl.fnSetColumnVis(parseInt($(this).attr("name"), 10), 
						$(this).is(":checked"));
				tbl.fnDraw();
			}).end()
			.dialog({
				title: "Show/Hide Columns",
				width: $(window).width() < 600 ? $(window).width() - 50 : 600, 
				height: $(window).height() < 400 ? $(window).height() - 50 : 400,
				close: function () {
					MyToolbarInit.oMyTable = null;
					$("#MyToolbar_showHide").remove(); 
				},
				buttons: {
					"Select All" :  function () {
						var tbl = MyToolbarInit.oMyTable;
						var cols = tbl.fnSettings().aoColumns.length;
						$(this).parents("div").find("input").attr("checked", true);
						for (var i = 0; i < cols; i++) {
							tbl.fnSetColumnVis(i, true);
						}
						tbl.fnDraw();
					},
					"Deselect All" : function () {
						var tbl = MyToolbarInit.oMyTable;
						var cols = tbl.fnSettings().aoColumns.length;
						$(this).parents("div").find("input").attr("checked", false);
						for (var i = 0; i < cols; i++) {
							tbl.fnSetColumnVis(i, false);
						}
						tbl.fnDraw();
					},
					"Reset Selection" : function () {
						var tbl = MyToolbarInit.oMyTable;
						var id = $(tbl).attr("id");
						var cols = tbl.fnSettings().aoColumns.length;
						var scols = MyToolbarInit.oMyTableStartCols[id];
						for (var i = 0; i < cols; i++) {
							tbl.fnSetColumnVis(i, scols[i]);
							$(this).parents("div").find("input[name='" + i + "']")
								.attr("checked", scols[i]);
						}
						tbl.fnDraw();
					},
					"Close" : function () {
						$("#MyToolbar_showHide").dialog("close");
					}
				}
			})
			.parents(".ui-dialog:first").addClass("MyToolbarPopup");
	};
	
	/** MyToolbar initialization
	 *  Creates the MyToolbar div, Included in the sDom as "M".
	 */
	function MyToolbar(oInit) {
		this.oInit = oInit;
		var buttons = MyToolbarInit.oButtons;
		var d = $("<div>").addClass("MyToolbar")[0];
		var loc = MyToolbarInit.MyToolbarLocation;
		for (var i in buttons) {
			if (buttons[i].show) {
				$("<div />").addClass("button").addClass(i)
					.attr("id", i).attr("title", buttons[i].title)
					.click(buttons[i].click)
					.css("background-image", "url(../js/jquery/media/images/" + i + ".gif)")
					.hover(function () {
						var loc = MyToolbarInit.MyToolbarLocation;
						var i = $(this).attr("id");
						$(this).toggleClass("button_hover")
							.css("background-image", "url(../js/jquery/media/images/" + i + 
								($(this).hasClass("button_hover") ? 
								"_hover.gif)" : ".gif)"));
					})
					.appendTo(d);
			}
		}
		return d;
	}
	
	/** Filter display object
	 *  Included in the sDom using "D". Will show the current filters for
	 *  each column. Shows as the height of the toolbar but expands on
	 *  mouseover to display all of the filters.
	 *  @param	oInit	the oInit object from data tables
	 */
	function FilterDisplay(oInit) {
		var d = $("<div />").addClass("FilterDisplay").hide()
			.hover(function () {
				if ($(this).css("position") == "relative")
					return;
				if ($(this).parent().find(".filterHover").length > 0)
					return;
				$(this).clone(false).addClass("filterHover")
					.css("top", $(this).position().top)
					.css("left", $(this).position().left)
					.css("cursor", "pointer")
					.stop().delay(400).animate({
						height: $(this).find("ul").outerHeight() + 50
					})
					.hover(function () {}, function () {
						$(this).stop().stop().remove();
					}).click(function () {
						$(this).parents(".dataTables_wrapper")
							.find(".MyToolbar .filter").trigger("click");
					})
					.appendTo($(this).parent());
			}, function (){});
		return d[0];
	}
	/** Update the filter display for the given table
	 *  @param	table	The table object
	 */
	MyToolbarInit.FilterDisplayUpdate = function (table) {
		var cols = table.fnSettings().aoColumns,
			scols = table.fnSettings().aoPreSearchCols,
			id = $(table.fnSettings().nTable).attr("id"),
			html = "", ntbl = false;
		if (MyToolbarInit.oMyTable == null) { 
			MyToolbarInit.oMyTable = table.fnSettings().nTable;
			var ntbl = true;
		}
		for (var i = 0; i < scols.length; i++) {
			var s = scols[i].sSearch;
			if (s != "") {
				var r = "";
				s = MyToolbarInit.fnStripRegex(s, "", i);
				if (MyToolbarInit.oMyTableFilterIncl[id] 
				    && MyToolbarInit.oMyTableFilterIncl[id][i]) {
					r = MyToolbarInit.oMyTableFilterIncl[id][i];
				}
				if (r != "") r = " (" + r + ")";
				html += "<li><b>" + cols[i].sTitle + r + ":</b> " + s + "</li>";
			}
		}	
		if (html != "") {
			html = "<h3>Filters:</h3><ul>" + html + "</ul>";
		}
		var d = $(table.fnSettings().nTable)
			.parents(".dataTables_wrapper:first")
			.find(".FilterDisplay");
		if (html == "") {
			$(d).html(html).hide();
		} else {
			$(d).html(html).show();
		}
		if (ntbl)
			MyToolbarInit.oMyTable = null;
	};
	
	/** Removes the extra regex characters
	 *  For displaying the current value
	 *  @param	value	The value to strip
	 *  @param	r		The current button text (includes, equals, etc).
	 */
	MyToolbarInit.fnStripRegex = function (value, r, col) {
		var opt = MyToolbarInit.MyToolbarFilterOptions;
		var found = false;
		if (r != "") {
			for (var i = 1; i < opt.length; i++) {
				var s = opt[i].start.length, e = opt[i].end.length;
				if (r == opt[i].text) {
					if (value.substr(0, s) == opt[i].start) {
						value = value.substr(s);
					}
					if (value.substr(value.length - e) == opt[i].end) {
						value = value.substr(0, value.length - e);
					}
					found = true;
					break;
				}
			}
		}
		if (!found && value != "") {
			var tbl = $(MyToolbarInit.oMyTable).attr("id");
			for (var i = opt.length - 1; i > 0; i--) {
				var s = opt[i].start.length, e = opt[i].end.length;
				if (value.substr(0, s) == opt[i].start 
					&& value.substr(value.length - e) == opt[i].end) {
					value = value.substr(s);
					value = value.substr(0, value.length - e);
					if (typeof MyToolbarInit.oMyTableFilterIncl[tbl] == 'undefined')
						MyToolbarInit.oMyTableFilterIncl[tbl] = [];
					MyToolbarInit.oMyTableFilterIncl[tbl][col] = opt[i].text;
					break;
				}
			}
		}
		if (value.indexOf("|") >= 0) { //for multiple select
			var temp = value.split("|"), value = "";
			for (var i = 0; i < temp.length; i++) {
				value += temp[i] + (i < temp.length - 1 ? " or " : "");
			}
		}
		return value;
	};
	
	/** Register new features with DataTables
	 */
	if (typeof $.fn.dataTable == "function"
		&& typeof $.fn.dataTableExt.sVersion != "undefined") {		
		//my toolbar section - filters and hide/show columns
		$.fn.dataTableExt.aoFeatures.push({
			"fnInit"    : function (oSettings) {
				return new MyToolbar({"oDTSettings": oSettings});
			},
			"cFeature"  : "M",
			"sFeature" : "MyToolbar"
		});
		//filters section - display current selection
		$.fn.dataTableExt.aoFeatures.push({
			"fnInit"	: function (oSettings) {
				return new FilterDisplay({"oDTSettings" : oSettings});
			},
			"cFeature"	: "D",
			"sFeature"	: "FilterDisplay"
		});
		
		if (typeof $.fn.dataTableExt.oApi.fnGetColumnData != "function") {
			//gets all of the data in a column
			$.fn.dataTableExt.oApi.fnGetColumnData = function (oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty) {
				if (typeof iColumn == "undefined") return new Array();
				if (typeof bUnique == "undefined") bUnique = true;
				if (typeof bFiltered == "undefined") bFiltered = true;
				if (typeof bIgnoreEmpty == "undefined") bIgnoreEmpty = true;
				var aiRows;
			//	if (bFiltered == true) aiRows = oSettings.aiDisplay; else 
				aiRows = oSettings.aiDisplayMaster; // all row numbers
				var asResultData = new Array();
				for (var i = 0, c = aiRows.length; i < c; i++) {
					iRow = aiRows[i];
					var aData = this.fnGetData(iRow);
					var sValue = aData[iColumn];
					if (bIgnoreEmpty == true && sValue.length == 0) continue;
					else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1) continue;
					else asResultData.push(sValue);
				}
				return asResultData;
			};
		}
	} else {
		alert("Warning: TableTools requires DataTables 1.5 or greater - "
				+ "www.datatables.net/download");
	}
	
})(jQuery);


/** Get the current script location
 *  So that we can use the path for images!
 *  @return	(String)	the path to MyToolbar.js
 */
function MyToolbar_getActiveScript(){
	var t = document.getElementsByTagName("script");
	return t[ t.length - 1 ];
}
