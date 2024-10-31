(function ($) {
	var pq = window.pq = window.pq || {
			flatten:function(t,e){var n,r=0,i=t.length;for(e=e||[];i>r;r++)n=t[r],null!=n&&(n.push?pq.flatten(n,e):e.push(n));return e}
		};

	var f = pq.formulas = {
		evalify: function(t, e) {
			var n, r, i = e.match(/([><=]{1,2})?(.*)/), o = i[1] || "=", a = i[2], l = this;
			return /(\*|\?)/.test(a) ? n = a.replace(/\*/g, ".*").replace(/\?/g, "\\S").replace(/\(/g, "\\(").replace(/\)/g, "\\)") : (o = "=" === o ? "==" : "<>" === o ? "!=" : o,
			r = this.isNumber(a)),
			t.map(function(t) {
				return n ? (t = null == t ? "" : t,
				t = ("<>" === o ? "!" : "") + "/^" + n + '$/i.test("' + t + '")') : r ? t = l.isNumber(t) ? t + o + a : "false" : (t = null == t ? "" : t,
				t = '"' + (t + "").toUpperCase() + '"' + o + '"' + (a + "").toUpperCase() + '"'),
				t
			})
		},
		get2Arr: function(t) {
			return t.get2Arr ? t.get2Arr() : t
		},
		isNumber: function(t) {
			return parseFloat(t) == t
		},
		_reduce: function(t, e) {
			var n = (t.length,
			[])
			  , r = e.map(function(t) {
				return []
			});
			return t.forEach(function(t, i) {
				null != t && (t = 1 * t,
				isNaN(t) || (n.push(t),
				r.forEach(function(t, n) {
					t.push(e[n][i])
				})))
			}),
			[n, r]
		},
		reduce: function(t) {
			t = this.toArray(t);
			var e = t.shift()
			  , n = t.filter(function(t, e) {
				return e % 2 == 0
			})
			  , r = this._reduce(e, n);
			return e = r[0],
			n = r[1],
			[e].concat(t.map(function(e, r) {
				return r % 2 == 0 ? n[r / 2] : t[r]
			}))
		},
		strDate1: "(\\d{1,2})/(\\d{1,2})/(\\d{2,4})",
		strDate2: "(\\d{4})-(\\d{1,2})-(\\d{1,2})",
		strTime: "(\\d{1,2})(:(\\d{1,2}))?(:(\\d{1,2}))?(\\s(AM|PM))?",
		isDate: function(t) {
			return this.reDate.test(t) && Date.parse(t) || t && t.constructor == Date
		},
		toArray: function(t) {
			for (var e = [], n = 0, r = t.length; r > n; n++)
				e.push(t[n]);
			return e
		},
		valueToDate: function(t) {
			var e = new Date(Date.UTC(1900, 0, 1));
			return e.setUTCDate(e.getUTCDate() + t - 2),
			e
		},
		varToDate: function(t) {
			var e, n, r, i, o;
			if (this.isNumber(t))
				e = this.valueToDate(t);
			else if (t.getTime)
				e = t;
			else if ("string" == typeof t) {
				if ((n = t.match(this.reDateTime)) ? n[12] ? (o = 1 * n[13],
				i = 1 * n[15],
				r = 1 * n[14]) : (r = 1 * n[2],
				i = 1 * n[3],
				o = 1 * n[4]) : (n = t.match(this.reDate2)) ? (o = 1 * n[1],
				i = 1 * n[3],
				r = 1 * n[2]) : (n = t.match(this.reDate1)) && (r = 1 * n[1],
				i = 1 * n[2],
				o = 1 * n[3]),
				!n)
					throw "#N/A date";
				t = Date.UTC(o, r - 1, i),
				e = new Date(t)
			}
			return e
		},
		_IFS: function(arg, fn) {
			for (var len = arg.length, i = 0, arr = [], a = 0; len > i; i += 2)
				arr.push(this.evalify(arg[i], arg[i + 1]));
			for (var condsIndx = arr[0].length, lenArr = len / 2, j; condsIndx--; ) {
				for (j = 0; lenArr > j && eval(arr[j][condsIndx]); j++)
					;
				a += j === lenArr ? fn(condsIndx) : 0
			}
			return a
		},
		ABS: function(t) {
			return Math.abs(t.map ? t[0] : t)
		},
		ACOS: function(t) {
			return Math.acos(t)
		},
		AND: function() {
			var arr = this.toArray(arguments);
			return eval(arr.join(" && "))
		},
		ASIN: function(t) {
			return Math.asin(t)
		},
		ATAN: function(t) {
			return Math.atan(t)
		},
		_AVERAGE: function(t) {
			var e = 0
			  , n = 0;
			if (t.forEach(function(t) {
				parseFloat(t) == t && (n += 1 * t,
				e++)
			}),
			e)
				return n / e;
			throw "#DIV/0!"
		},
		AVERAGE: function() {
			return this._AVERAGE(pq.flatten(arguments))
		},
		AVERAGEIF: function(t, e, n) {
			return this.AVERAGEIFS(n || t, t, e)
		},
		AVERAGEIFS: function() {
			var t = this.reduce(arguments)
			  , e = 0
			  , n = t.shift()
			  , r = this._IFS(t, function(t) {
				return e++,
				n[t]
			});
			if (!e)
				throw "#DIV/0!";
			return r / e
		},
		TRUE: !0,
		FALSE: !1,
		CEILING: function(t) {
			return Math.ceil(t)
		},
		CHAR: function(t) {
			return String.fromCharCode(t)
		},
		CHOOSE: function() {
			var t = pq.flatten(arguments)
			  , e = t[0];
			if (e > 0 && e < t.length)
				return t[e];
			throw "#VALUE!"
		},
		CODE: function(t) {
			return (t + "").charCodeAt(0)
		},
		COLUMN: function(t) {
			return (t || this).getRange().c1 + 1
		},
		COLUMNS: function(t) {
			var e = t.getRange();
			return e.c2 - e.c1 + 1
		},
		CONCATENATE: function() {
			var t = pq.flatten(arguments)
			  , e = "";
			return t.forEach(function(t) {
				e += t
			}),
			e
		},
		COS: function(t) {
			return Math.cos(t)
		},
		_COUNT: function(t) {
			var e = pq.flatten(t)
			  , n = this
			  , r = 0
			  , i = 0
			  , o = 0;
			return e.forEach(function(t) {
				null == t || "" === t ? r++ : (i++,
				n.isNumber(t) && o++)
			}),
			[r, i, o]
		},
		COUNT: function() {
			return this._COUNT(arguments)[2]
		},
		COUNTA: function() {
			return this._COUNT(arguments)[1]
		},
		COUNTBLANK: function() {
			return this._COUNT(arguments)[0]
		},
		COUNTIF: function(t, e) {
			return this.COUNTIFS(t, e)
		},
		COUNTIFS: function() {
			return this._IFS(arguments, function() {
				return 1
			})
		},
		DATE: function(t, e, n) {
			if (0 > t || t > 9999)
				throw "#NUM!";
			return 1899 >= t && (t += 1900),
			this.VALUE(new Date(Date.UTC(t, e - 1, n)))
		},
		DATEVALUE: function(t) {
			return this.DATEDIF("1/1/1900", t, "D") + 2
		},
		DATEDIF: function(t, e, n) {
			var r, i = this.varToDate(e), o = this.varToDate(t), a = i.getTime(), l = o.getTime(), s = (a - l) / 864e5;
			if ("Y" === n)
				return parseInt(s / 365);
			if ("M" === n)
				return r = i.getUTCMonth() - o.getUTCMonth() + 12 * (i.getUTCFullYear() - o.getUTCFullYear()),
				o.getUTCDate() > i.getUTCDate() && r--,
				r;
			if ("D" === n)
				return s;
			throw "unit N/A"
		},
		DAY: function(t) {
			return this.varToDate(t).getUTCDate()
		},
		DAYS: function(t, e) {
			return this.DATEDIF(e, t, "D")
		},
		DEGREES: function(t) {
			return 180 / Math.PI * t
		},
		EOMONTH: function(t, e) {
			e = e || 0;
			var n = this.varToDate(t);
			return n.setUTCMonth(n.getUTCMonth() + e + 1),
			n.setUTCDate(0),
			this.VALUE(n)
		},
		EXP: function(t) {
			return Math.exp(t)
		},
		FIND: function(t, e, n) {
			return e.indexOf(t, n ? n - 1 : 0) + 1
		},
		FLOOR: function(t, e) {
			return 0 > t * e ? "#NUM!" : parseInt(t / e) * e
		},
		HLOOKUP: function(t, e, n, r) {
			null == r && (r = !0),
			e = this.get2Arr(e);
			var i = this.MATCH(t, e[0], r ? 1 : 0);
			return this.INDEX(e, n, i)
		},
		HOUR: function(t) {
			if (Date.parse(t)) {
				var e = new Date(t);
				return e.getHours()
			}
			return 24 * t
		},
		IF: function(t, e, n) {
			return t ? e : n
		},
		INDEX: function(t, e, n) {
			return t = this.get2Arr(t),
			e = e || 1,
			n = n || 1,
			"function" == typeof t[0].push ? t[e - 1][n - 1] : t[e > 1 ? e - 1 : n - 1]
		},
		INDIRECT: function(t) {
			return this.iFormula.range(t)
		},
		ISBLANK: function(t) {
			return "" === t
		},
		LARGE: function(t, e) {
			return t.sort(),
			t[t.length - (e || 1)]
		},
		LEFT: function(t, e) {
			return t.substr(0, e || 1)
		},
		LEN: function(t) {
			return t = (t.map ? t : [t]).map(function(t) {
				return t.length
			}),
			t.length > 1 ? t : t[0]
		},
		LOOKUP: function(t, e, n) {
			n = n || e;
			var r = this.MATCH(t, e, 1);
			return this.INDEX(n, 1, r)
		},
		LOWER: function(t) {
			return (t + "").toLocaleLowerCase()
		},
		_MAXMIN: function(t, e) {
			var n, r = this;
			return t.forEach(function(t) {
				null != t && (t = r.VALUE(t),
				r.isNumber(t) && (t * e > n * e || null == n) && (n = t))
			}),
			null != n ? n : 0
		},
		MATCH: function(val, arr, type) {
			var isNumber = this.isNumber(val), _isNumber, sign, indx, _val, i = 0, len = arr.length;
			if (null == type && (type = 1),
			val = isNumber ? val : val.toUpperCase(),
			0 === type) {
				for (arr = this.evalify(arr, val + ""),
				i = 0; len > i; i++)
					if (_val = arr[i],
					eval(_val)) {
						indx = i + 1;
						break
					}
			} else {
				for (i = 0; len > i; i++)
					if (_val = arr[i],
					_isNumber = this.isNumber(_val),
					_val = arr[i] = _isNumber ? _val : _val ? _val.toUpperCase() : "",
					val == _val) {
						indx = i + 1;
						break
					}
				if (!indx) {
					for (i = 0; len > i; i++)
						if (_val = arr[i],
						_isNumber = this.isNumber(_val),
						type * (val > _val ? -1 : 1) === 1 && isNumber == _isNumber) {
							indx = i;
							break
						}
					indx = null == indx ? i : indx
				}
			}
			if (indx)
				return indx;
			throw "#N/A"
		},
		MAX: function() {
			var t = pq.flatten(arguments);
			return this._MAXMIN(t, 1)
		},
		MEDIAN: function() {
			var t = pq.flatten(arguments).filter(function(t) {
				return 1 * t == t
			}).sort(function(t, e) {
				return e - t
			})
			  , e = t.length
			  , n = e / 2;
			return n === parseInt(n) ? (t[n - 1] + t[n]) / 2 : t[(e - 1) / 2]
		},
		MID: function(t, e, n) {
			if (1 > e || 0 > n)
				throw "#VALUE!";
			return t.substr(e - 1, n)
		},
		MIN: function() {
			var t = pq.flatten(arguments);
			return this._MAXMIN(t, -1)
		},
		MODE: function() {
			var t, e, n = pq.flatten(arguments), r = {}, i = 0;
			if (n.forEach(function(n) {
				t = r[n] = r[n] ? r[n] + 1 : 1,
				t > i && (i = t,
				e = n)
			}),
			2 > i)
				throw "#N/A";
			return e
		},
		MONTH: function(t) {
			return this.varToDate(t).getUTCMonth() + 1
		},
		OR: function() {
			var arr = this.toArray(arguments);
			return eval(arr.join(" || "))
		},
		PI: function() {
			return Math.PI
		},
		POWER: function(t, e) {
			return Math.pow(t, e)
		},
		PRODUCT: function() {
			var t = pq.flatten(arguments)
			  , e = 1;
			return t.forEach(function(t) {
				e *= t
			}),
			e
		},
		PROPER: function(t) {
			return t = t.replace(/(\S+)/g, function(t) {
				return t.charAt(0).toUpperCase() + t.substr(1).toLowerCase()
			})
		},
		RADIANS: function(t) {
			return Math.PI / 180 * t
		},
		RAND: function() {
			return Math.random()
		},
		RANK: function(t, e, n) {
			var r = JSON.stringify(e.getRange())
			  , i = this
			  , o = r + "_range";
			e = this[o] || function() {
				return i[o] = e,
				e.sort(function(t, e) {
					return t - e
				})
			}();
			for (var a = 0, l = e.length; l > a; a++)
				if (t === e[a])
					return n ? a + 1 : l - a
		},
		RATE: function() {},
		REPLACE: function(t, e, n, r) {
			return t += "",
			t.substr(0, e - 1) + r + t.substr(e + n - 1)
		},
		REPT: function(t, e) {
			for (var n = ""; e--; )
				n += t;
			return n
		},
		RIGHT: function(t, e) {
			return e = e || 1,
			t.substr(-1 * e, e)
		},
		_ROUND: function(t, e, n) {
			var r = Math.pow(10, e)
			  , i = t * r
			  , o = parseInt(i)
			  , a = i - o;
			return n(o, a) / r
		},
		ROUND: function(t, e) {
			return this._ROUND(t, e, function(t, e) {
				var n = Math.abs(e);
				return t + (n >= .5 ? n / e : 0)
			})
		},
		ROUNDDOWN: function(t, e) {
			return this._ROUND(t, e, function(t) {
				return t
			})
		},
		ROUNDUP: function(t, e) {
			return this._ROUND(t, e, function(t, e) {
				return t + (e ? Math.abs(e) / e : 0)
			})
		},
		ROW: function(t) {
			return (t || this).getRange().r1 + 1
		},
		ROWS: function(t) {
			var e = t.getRange();
			return e.r2 - e.r1 + 1
		},
		SEARCH: function(t, e, n) {
			return t = t.toUpperCase(),
			e = e.toUpperCase(),
			e.indexOf(t, n ? n - 1 : 0) + 1
		},
		SIN: function(t) {
			return Math.sin(t)
		},
		SMALL: function(t, e) {
			return t.sort(),
			t[(e || 1) - 1]
		},
		SQRT: function(t) {
			return Math.sqrt(t)
		},
		_STDEV: function(t) {
			t = pq.flatten(t);
			var e = t.length
			  , n = this._AVERAGE(t)
			  , r = 0;
			return t.forEach(function(t) {
				r += (t - n) * (t - n)
			}),
			[r, e]
		},
		STDEV: function() {
			var t = this._STDEV(arguments);
			if (1 === t[1])
				throw "#DIV/0!";
			return Math.sqrt(t[0] / (t[1] - 1))
		},
		STDEVP: function() {
			var t = this._STDEV(arguments);
			return Math.sqrt(t[0] / t[1])
		},
		SUBSTITUTE: function(t, e, n, r) {
			var i = 0;
			return t.replace(new RegExp(e,"g"), function(t) {
				return i++,
				r ? i === r ? n : e : n
			})
		},
		SUM: function() {
			var t = pq.flatten(arguments)
			  , e = 0
			  , n = this;
			return t.forEach(function(t) {
				t = n.VALUE(t),
				n.isNumber(t) && (e += parseFloat(t))
			}),
			e
		},
		SUMIF: function(t, e, n) {
			return this.SUMIFS(n || t, t, e)
		},
		SUMIFS: function() {
			var t = this.reduce(arguments)
			  , e = t.shift();
			return this._IFS(t, function(t) {
				return e[t]
			})
		},
		SUMPRODUCT: function() {
			var t = this.toArray(arguments);
			return t = t[0].map(function(e, n) {
				var r = 1;
				return t.forEach(function(t) {
					var e = t[n];
					r *= parseFloat(e) == e ? e : 0
				}),
				r
			}),
			pq.aggregate.sum(t)
		},
		TAN: function(t) {
			return Math.tan(t)
		},
		TEXT: function(t, e) {
			return this.isNumber(t) && e.indexOf("#") >= 0 ? pq.formatNumber(t, e) : $.datepicker.formatDate(pq.excelToJui(e), this.varToDate(t))
		},
		TIME: function(t, e, n) {
			return (t + e / 60 + n / 3600) / 24
		},
		TIMEVALUE: function(t) {
			var e = t.match(this.reTime);
			if (e && null != e[1] && (null != e[3] || null != e[7]))
				var n = 1 * e[1]
				  , r = 1 * (e[3] || 0)
				  , i = 1 * (e[5] || 0)
				  , o = (e[7] || "").toUpperCase()
				  , a = n + r / 60 + i / 3600;
			if (a >= 0 && (o && 13 > a || !o && 24 > a))
				return "PM" == o && 12 > n ? a += 12 : "AM" == o && 12 == n && (a -= 12),
				a / 24;
			throw "#VALUE!"
		},
		TODAY: function() {
			var t = new Date;
			return this.VALUE(new Date(Date.UTC(t.getFullYear(), t.getMonth(), t.getDate())))
		},
		TRIM: function(t) {
			return t.replace(/^\s+|\s+$/gm, "")
		},
		TRUNC: function(t, e) {
			return e = Math.pow(10, e || 0),
			~~(t * e) / e
		},
		UPPER: function(t) {
			return (t + "").toLocaleUpperCase()
		},
		VALUE: function(t) {
			var e, n;
			if (t)
				if (parseFloat(t) == t)
					n = parseFloat(t);
				else if (this.isDate(t))
					n = this.DATEVALUE(t);
				else if (e = t.match(this.reDateTime)) {
					var r = e[1] || e[12]
					  , i = t.substr(r.length + 1);
					n = this.DATEVALUE(r) + this.TIMEVALUE(i)
				} else
					(e = t.match(this.reTime)) ? n = this.TIMEVALUE(t) : (n = t.replace(/[^0-9\-.]/g, ""),
					n = n.replace(/(\.[1-9]*)0+$/, "$1").replace(/\.$/, ""));
			else
				n = 0;
			return n
		},
		VAR: function() {
			var t = this._STDEV(arguments);
			return t[0] / (t[1] - 1)
		},
		VARP: function() {
			var t = this._STDEV(arguments);
			return t[0] / t[1]
		},
		VLOOKUP: function(t, e, n, r) {
			null == r && (r = !0),
			e = this.get2Arr(e);
			var i = e.map(function(t) {
				return t[0]
			})
			  , o = this.MATCH(t, i, r ? 1 : 0);
			return this.INDEX(e, o, n)
		},
		YEAR: function(t) {
			return this.varToDate(t).getUTCFullYear()
		}
	};
	f.reDate1 = new RegExp("^" + f.strDate1 + "$"),
	f.reDate2 = new RegExp("^" + f.strDate2 + "$"),
	f.reDate = new RegExp("^" + f.strDate1 + "$|^" + f.strDate2 + "$"),
	f.reTime = new RegExp("^" + f.strTime + "$","i"),
	f.reDateTime = new RegExp("^(" + f.strDate1 + ")\\s" + f.strTime + "$|^(" + f.strDate2 + ")\\s" + f.strTime + "$")
}(window.jQuery));