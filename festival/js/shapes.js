
!function(t, e) {
    "use strict";
    "object" == typeof module && "object" == typeof module.exports ? module.exports = t.document ? e(t, !0) : function(t) {
        if (!t.document)
            throw new Error("jQuery requires a window with a document");
        return e(t)
    }
    : e(t)
}("undefined" != typeof window ? window : this, function(T, t) {
    "use strict";
    var e = []
      , C = T.document
      , n = Object.getPrototypeOf
      , a = e.slice
      , g = e.concat
      , l = e.push
      , r = e.indexOf
      , i = {}
      , o = i.toString
      , _ = i.hasOwnProperty
      , s = _.toString
      , u = s.call(Object)
      , m = {}
      , v = function(t) {
        return "function" == typeof t && "number" != typeof t.nodeType
    }
      , y = function(t) {
        return null != t && t === t.window
    }
      , c = {
        type: !0,
        src: !0,
        noModule: !0
    };
    function w(t, e, i) {
        var n, r = (e = e || C).createElement("script");
        if (r.text = t,
        i)
            for (n in c)
                i[n] && (r[n] = i[n]);
        e.head.appendChild(r).parentNode.removeChild(r)
    }
    function b(t) {
        return null == t ? t + "" : "object" == typeof t || "function" == typeof t ? i[o.call(t)] || "object" : typeof t
    }
    var k = function(t, e) {
        return new k.fn.init(t,e)
    }
      , h = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;
    function f(t) {
        var e = !!t && "length"in t && t.length
          , i = b(t);
        return !v(t) && !y(t) && ("array" === i || 0 === e || "number" == typeof e && 0 < e && e - 1 in t)
    }
    k.fn = k.prototype = {
        jquery: "3.3.1",
        constructor: k,
        length: 0,
        toArray: function() {
            return a.call(this)
        },
        get: function(t) {
            return null == t ? a.call(this) : t < 0 ? this[t + this.length] : this[t]
        },
        pushStack: function(t) {
            var e = k.merge(this.constructor(), t);
            return e.prevObject = this,
            e
        },
        each: function(t) {
            return k.each(this, t)
        },
        map: function(i) {
            return this.pushStack(k.map(this, function(t, e) {
                return i.call(t, e, t)
            }))
        },
        slice: function() {
            return this.pushStack(a.apply(this, arguments))
        },
        first: function() {
            return this.eq(0)
        },
        last: function() {
            return this.eq(-1)
        },
        eq: function(t) {
            var e = this.length
              , i = +t + (t < 0 ? e : 0);
            return this.pushStack(0 <= i && i < e ? [this[i]] : [])
        },
        end: function() {
            return this.prevObject || this.constructor()
        },
        push: l,
        sort: e.sort,
        splice: e.splice
    },
    k.extend = k.fn.extend = function() {
        var t, e, i, n, r, o, s = arguments[0] || {}, a = 1, l = arguments.length, u = !1;
        for ("boolean" == typeof s && (u = s,
        s = arguments[a] || {},
        a++),
        "object" == typeof s || v(s) || (s = {}),
        a === l && (s = this,
        a--); a < l; a++)
            if (null != (t = arguments[a]))
                for (e in t)
                    i = s[e],
                    s !== (n = t[e]) && (u && n && (k.isPlainObject(n) || (r = Array.isArray(n))) ? (o = r ? (r = !1,
                    i && Array.isArray(i) ? i : []) : i && k.isPlainObject(i) ? i : {},
                    s[e] = k.extend(u, o, n)) : void 0 !== n && (s[e] = n));
        return s
    }
    ,
    k.extend({
        expando: "jQuery" + ("3.3.1" + Math.random()).replace(/\D/g, ""),
        isReady: !0,
        error: function(t) {
            throw new Error(t)
        },
        noop: function() {},
        isPlainObject: function(t) {
            var e, i;
            return !(!t || "[object Object]" !== o.call(t) || (e = n(t)) && ("function" != typeof (i = _.call(e, "constructor") && e.constructor) || s.call(i) !== u))
        },
        isEmptyObject: function(t) {
            var e;
            for (e in t)
                return !1;
            return !0
        },
        globalEval: function(t) {
            w(t)
        },
        each: function(t, e) {
            var i, n = 0;
            if (f(t))
                for (i = t.length; n < i && !1 !== e.call(t[n], n, t[n]); n++)
                    ;
            else
                for (n in t)
                    if (!1 === e.call(t[n], n, t[n]))
                        break;
            return t
        },
        trim: function(t) {
            return null == t ? "" : (t + "").replace(h, "")
        },
        makeArray: function(t, e) {
            var i = e || [];
            return null != t && (f(Object(t)) ? k.merge(i, "string" == typeof t ? [t] : t) : l.call(i, t)),
            i
        },
        inArray: function(t, e, i) {
            return null == e ? -1 : r.call(e, t, i)
        },
        merge: function(t, e) {
            for (var i = +e.length, n = 0, r = t.length; n < i; n++)
                t[r++] = e[n];
            return t.length = r,
            t
        },
        grep: function(t, e, i) {
            for (var n = [], r = 0, o = t.length, s = !i; r < o; r++)
                !e(t[r], r) !== s && n.push(t[r]);
            return n
        },
        map: function(t, e, i) {
            var n, r, o = 0, s = [];
            if (f(t))
                for (n = t.length; o < n; o++)
                    null != (r = e(t[o], o, i)) && s.push(r);
            else
                for (o in t)
                    null != (r = e(t[o], o, i)) && s.push(r);
            return g.apply([], s)
        },
        guid: 1,
        support: m
    }),
    "function" == typeof Symbol && (k.fn[Symbol.iterator] = e[Symbol.iterator]),
    k.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "), function(t, e) {
        i["[object " + e + "]"] = e.toLowerCase()
    });
    var d = function(i) {
        var t, d, w, o, r, p, h, g, b, l, u, x, T, s, C, _, a, c, m, k = "sizzle" + 1 * new Date, v = i.document, S = 0, n = 0, f = st(), y = st(), $ = st(), D = function(t, e) {
            return t === e && (u = !0),
            0
        }, A = {}.hasOwnProperty, e = [], E = e.pop, P = e.push, O = e.push, R = e.slice, M = function(t, e) {
            for (var i = 0, n = t.length; i < n; i++)
                if (t[i] === e)
                    return i;
            return -1
        }, j = "checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped", z = "[\\x20\\t\\r\\n\\f]", F = "(?:\\\\.|[\\w-]|[^\0-\\xa0])+", I = "\\[" + z + "*(" + F + ")(?:" + z + "*([*^$|!~]?=)" + z + "*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|(" + F + "))|)" + z + "*\\]", N = ":(" + F + ")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|" + I + ")*)|.*)\\)|)", B = new RegExp(z + "+","g"), L = new RegExp("^" + z + "+|((?:^|[^\\\\])(?:\\\\.)*)" + z + "+$","g"), q = new RegExp("^" + z + "*," + z + "*"), W = new RegExp("^" + z + "*([>+~]|" + z + ")" + z + "*"), H = new RegExp("=" + z + "*([^\\]'\"]*?)" + z + "*\\]","g"), U = new RegExp(N), V = new RegExp("^" + F + "$"), X = {
            ID: new RegExp("^#(" + F + ")"),
            CLASS: new RegExp("^\\.(" + F + ")"),
            TAG: new RegExp("^(" + F + "|[*])"),
            ATTR: new RegExp("^" + I),
            PSEUDO: new RegExp("^" + N),
            CHILD: new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(" + z + "*(even|odd|(([+-]|)(\\d*)n|)" + z + "*(?:([+-]|)" + z + "*(\\d+)|))" + z + "*\\)|)","i"),
            bool: new RegExp("^(?:" + j + ")$","i"),
            needsContext: new RegExp("^" + z + "*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\(" + z + "*((?:-\\d)?\\d*)" + z + "*\\)|)(?=[^-]|$)","i")
        }, G = /^(?:input|select|textarea|button)$/i, Y = /^h\d$/i, Z = /^[^{]+\{\s*\[native \w/, Q = /^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/, K = /[+~]/, J = new RegExp("\\\\([\\da-f]{1,6}" + z + "?|(" + z + ")|.)","ig"), tt = function(t, e, i) {
            var n = "0x" + e - 65536;
            return n != n || i ? e : n < 0 ? String.fromCharCode(n + 65536) : String.fromCharCode(n >> 10 | 55296, 1023 & n | 56320)
        }, et = /([\0-\x1f\x7f]|^-?\d)|^-$|[^\0-\x1f\x7f-\uFFFF\w-]/g, it = function(t, e) {
            return e ? "\0" === t ? "�" : t.slice(0, -1) + "\\" + t.charCodeAt(t.length - 1).toString(16) + " " : "\\" + t
        }, nt = function() {
            x()
        }, rt = vt(function(t) {
            return !0 === t.disabled && ("form"in t || "label"in t)
        }, {
            dir: "parentNode",
            next: "legend"
        });
        try {
            O.apply(e = R.call(v.childNodes), v.childNodes),
            e[v.childNodes.length].nodeType
        } catch (i) {
            O = {
                apply: e.length ? function(t, e) {
                    P.apply(t, R.call(e))
                }
                : function(t, e) {
                    for (var i = t.length, n = 0; t[i++] = e[n++]; )
                        ;
                    t.length = i - 1
                }
            }
        }
        function ot(t, e, i, n) {
            var r, o, s, a, l, u, c, h = e && e.ownerDocument, f = e ? e.nodeType : 9;
            if (i = i || [],
            "string" != typeof t || !t || 1 !== f && 9 !== f && 11 !== f)
                return i;
            if (!n && ((e ? e.ownerDocument || e : v) !== T && x(e),
            e = e || T,
            C)) {
                if (11 !== f && (l = Q.exec(t)))
                    if (r = l[1]) {
                        if (9 === f) {
                            if (!(s = e.getElementById(r)))
                                return i;
                            if (s.id === r)
                                return i.push(s),
                                i
                        } else if (h && (s = h.getElementById(r)) && m(e, s) && s.id === r)
                            return i.push(s),
                            i
                    } else {
                        if (l[2])
                            return O.apply(i, e.getElementsByTagName(t)),
                            i;
                        if ((r = l[3]) && d.getElementsByClassName && e.getElementsByClassName)
                            return O.apply(i, e.getElementsByClassName(r)),
                            i
                    }
                if (d.qsa && !$[t + " "] && (!_ || !_.test(t))) {
                    if (1 !== f)
                        h = e,
                        c = t;
                    else if ("object" !== e.nodeName.toLowerCase()) {
                        for ((a = e.getAttribute("id")) ? a = a.replace(et, it) : e.setAttribute("id", a = k),
                        o = (u = p(t)).length; o--; )
                            u[o] = "#" + a + " " + mt(u[o]);
                        c = u.join(","),
                        h = K.test(t) && gt(e.parentNode) || e
                    }
                    if (c)
                        try {
                            return O.apply(i, h.querySelectorAll(c)),
                            i
                        } catch (t) {} finally {
                            a === k && e.removeAttribute("id")
                        }
                }
            }
            return g(t.replace(L, "$1"), e, i, n)
        }
        function st() {
            var n = [];
            return function t(e, i) {
                return n.push(e + " ") > w.cacheLength && delete t[n.shift()],
                t[e + " "] = i
            }
        }
        function at(t) {
            return t[k] = !0,
            t
        }
        function lt(t) {
            var e = T.createElement("fieldset");
            try {
                return !!t(e)
            } catch (t) {
                return !1
            } finally {
                e.parentNode && e.parentNode.removeChild(e),
                e = null
            }
        }
        function ut(t, e) {
            for (var i = t.split("|"), n = i.length; n--; )
                w.attrHandle[i[n]] = e
        }
        function ct(t, e) {
            var i = e && t
              , n = i && 1 === t.nodeType && 1 === e.nodeType && t.sourceIndex - e.sourceIndex;
            if (n)
                return n;
            if (i)
                for (; i = i.nextSibling; )
                    if (i === e)
                        return -1;
            return t ? 1 : -1
        }
        function ht(e) {
            return function(t) {
                return "input" === t.nodeName.toLowerCase() && t.type === e
            }
        }
        function ft(i) {
            return function(t) {
                var e = t.nodeName.toLowerCase();
                return ("input" === e || "button" === e) && t.type === i
            }
        }
        function dt(e) {
            return function(t) {
                return "form"in t ? t.parentNode && !1 === t.disabled ? "label"in t ? "label"in t.parentNode ? t.parentNode.disabled === e : t.disabled === e : t.isDisabled === e || t.isDisabled !== !e && rt(t) === e : t.disabled === e : "label"in t && t.disabled === e
            }
        }
        function pt(s) {
            return at(function(o) {
                return o = +o,
                at(function(t, e) {
                    for (var i, n = s([], t.length, o), r = n.length; r--; )
                        t[i = n[r]] && (t[i] = !(e[i] = t[i]))
                })
            })
        }
        function gt(t) {
            return t && void 0 !== t.getElementsByTagName && t
        }
        for (t in d = ot.support = {},
        r = ot.isXML = function(t) {
            var e = t && (t.ownerDocument || t).documentElement;
            return !!e && "HTML" !== e.nodeName
        }
        ,
        x = ot.setDocument = function(t) {
            var e, i, n = t ? t.ownerDocument || t : v;
            return n !== T && 9 === n.nodeType && n.documentElement && (s = (T = n).documentElement,
            C = !r(T),
            v !== T && (i = T.defaultView) && i.top !== i && (i.addEventListener ? i.addEventListener("unload", nt, !1) : i.attachEvent && i.attachEvent("onunload", nt)),
            d.attributes = lt(function(t) {
                return t.className = "i",
                !t.getAttribute("className")
            }),
            d.getElementsByTagName = lt(function(t) {
                return t.appendChild(T.createComment("")),
                !t.getElementsByTagName("*").length
            }),
            d.getElementsByClassName = Z.test(T.getElementsByClassName),
            d.getById = lt(function(t) {
                return s.appendChild(t).id = k,
                !T.getElementsByName || !T.getElementsByName(k).length
            }),
            d.getById ? (w.filter.ID = function(t) {
                var e = t.replace(J, tt);
                return function(t) {
                    return t.getAttribute("id") === e
                }
            }
            ,
            w.find.ID = function(t, e) {
                if (void 0 !== e.getElementById && C) {
                    var i = e.getElementById(t);
                    return i ? [i] : []
                }
            }
            ) : (w.filter.ID = function(t) {
                var i = t.replace(J, tt);
                return function(t) {
                    var e = void 0 !== t.getAttributeNode && t.getAttributeNode("id");
                    return e && e.value === i
                }
            }
            ,
            w.find.ID = function(t, e) {
                if (void 0 !== e.getElementById && C) {
                    var i, n, r, o = e.getElementById(t);
                    if (o) {
                        if ((i = o.getAttributeNode("id")) && i.value === t)
                            return [o];
                        for (r = e.getElementsByName(t),
                        n = 0; o = r[n++]; )
                            if ((i = o.getAttributeNode("id")) && i.value === t)
                                return [o]
                    }
                    return []
                }
            }
            ),
            w.find.TAG = d.getElementsByTagName ? function(t, e) {
                return void 0 !== e.getElementsByTagName ? e.getElementsByTagName(t) : d.qsa ? e.querySelectorAll(t) : void 0
            }
            : function(t, e) {
                var i, n = [], r = 0, o = e.getElementsByTagName(t);
                if ("*" !== t)
                    return o;
                for (; i = o[r++]; )
                    1 === i.nodeType && n.push(i);
                return n
            }
            ,
            w.find.CLASS = d.getElementsByClassName && function(t, e) {
                if (void 0 !== e.getElementsByClassName && C)
                    return e.getElementsByClassName(t)
            }
            ,
            a = [],
            _ = [],
            (d.qsa = Z.test(T.querySelectorAll)) && (lt(function(t) {
                s.appendChild(t).innerHTML = "<a id='" + k + "'></a><select id='" + k + "-\r\\' msallowcapture=''><option selected=''></option></select>",
                t.querySelectorAll("[msallowcapture^='']").length && _.push("[*^$]=" + z + "*(?:''|\"\")"),
                t.querySelectorAll("[selected]").length || _.push("\\[" + z + "*(?:value|" + j + ")"),
                t.querySelectorAll("[id~=" + k + "-]").length || _.push("~="),
                t.querySelectorAll(":checked").length || _.push(":checked"),
                t.querySelectorAll("a#" + k + "+*").length || _.push(".#.+[+~]")
            }),
            lt(function(t) {
                t.innerHTML = "<a href='' disabled='disabled'></a><select disabled='disabled'><option/></select>";
                var e = T.createElement("input");
                e.setAttribute("type", "hidden"),
                t.appendChild(e).setAttribute("name", "D"),
                t.querySelectorAll("[name=d]").length && _.push("name" + z + "*[*^$|!~]?="),
                2 !== t.querySelectorAll(":enabled").length && _.push(":enabled", ":disabled"),
                s.appendChild(t).disabled = !0,
                2 !== t.querySelectorAll(":disabled").length && _.push(":enabled", ":disabled"),
                t.querySelectorAll("*,:x"),
                _.push(",.*:")
            })),
            (d.matchesSelector = Z.test(c = s.matches || s.webkitMatchesSelector || s.mozMatchesSelector || s.oMatchesSelector || s.msMatchesSelector)) && lt(function(t) {
                d.disconnectedMatch = c.call(t, "*"),
                c.call(t, "[s!='']:x"),
                a.push("!=", N)
            }),
            _ = _.length && new RegExp(_.join("|")),
            a = a.length && new RegExp(a.join("|")),
            e = Z.test(s.compareDocumentPosition),
            m = e || Z.test(s.contains) ? function(t, e) {
                var i = 9 === t.nodeType ? t.documentElement : t
                  , n = e && e.parentNode;
                return t === n || !(!n || 1 !== n.nodeType || !(i.contains ? i.contains(n) : t.compareDocumentPosition && 16 & t.compareDocumentPosition(n)))
            }
            : function(t, e) {
                if (e)
                    for (; e = e.parentNode; )
                        if (e === t)
                            return !0;
                return !1
            }
            ,
            D = e ? function(t, e) {
                if (t === e)
                    return u = !0,
                    0;
                var i = !t.compareDocumentPosition - !e.compareDocumentPosition;
                return i || (1 & (i = (t.ownerDocument || t) === (e.ownerDocument || e) ? t.compareDocumentPosition(e) : 1) || !d.sortDetached && e.compareDocumentPosition(t) === i ? t === T || t.ownerDocument === v && m(v, t) ? -1 : e === T || e.ownerDocument === v && m(v, e) ? 1 : l ? M(l, t) - M(l, e) : 0 : 4 & i ? -1 : 1)
            }
            : function(t, e) {
                if (t === e)
                    return u = !0,
                    0;
                var i, n = 0, r = t.parentNode, o = e.parentNode, s = [t], a = [e];
                if (!r || !o)
                    return t === T ? -1 : e === T ? 1 : r ? -1 : o ? 1 : l ? M(l, t) - M(l, e) : 0;
                if (r === o)
                    return ct(t, e);
                for (i = t; i = i.parentNode; )
                    s.unshift(i);
                for (i = e; i = i.parentNode; )
                    a.unshift(i);
                for (; s[n] === a[n]; )
                    n++;
                return n ? ct(s[n], a[n]) : s[n] === v ? -1 : a[n] === v ? 1 : 0
            }
            ),
            T
        }
        ,
        ot.matches = function(t, e) {
            return ot(t, null, null, e)
        }
        ,
        ot.matchesSelector = function(t, e) {
            if ((t.ownerDocument || t) !== T && x(t),
            e = e.replace(H, "='$1']"),
            d.matchesSelector && C && !$[e + " "] && (!a || !a.test(e)) && (!_ || !_.test(e)))
                try {
                    var i = c.call(t, e);
                    if (i || d.disconnectedMatch || t.document && 11 !== t.document.nodeType)
                        return i
                } catch (t) {}
            return 0 < ot(e, T, null, [t]).length
        }
        ,
        ot.contains = function(t, e) {
            return (t.ownerDocument || t) !== T && x(t),
            m(t, e)
        }
        ,
        ot.attr = function(t, e) {
            (t.ownerDocument || t) !== T && x(t);
            var i = w.attrHandle[e.toLowerCase()]
              , n = i && A.call(w.attrHandle, e.toLowerCase()) ? i(t, e, !C) : void 0;
            return void 0 !== n ? n : d.attributes || !C ? t.getAttribute(e) : (n = t.getAttributeNode(e)) && n.specified ? n.value : null
        }
        ,
        ot.escape = function(t) {
            return (t + "").replace(et, it)
        }
        ,
        ot.error = function(t) {
            throw new Error("Syntax error, unrecognized expression: " + t)
        }
        ,
        ot.uniqueSort = function(t) {
            var e, i = [], n = 0, r = 0;
            if (u = !d.detectDuplicates,
            l = !d.sortStable && t.slice(0),
            t.sort(D),
            u) {
                for (; e = t[r++]; )
                    e === t[r] && (n = i.push(r));
                for (; n--; )
                    t.splice(i[n], 1)
            }
            return l = null,
            t
        }
        ,
        o = ot.getText = function(t) {
            var e, i = "", n = 0, r = t.nodeType;
            if (r) {
                if (1 === r || 9 === r || 11 === r) {
                    if ("string" == typeof t.textContent)
                        return t.textContent;
                    for (t = t.firstChild; t; t = t.nextSibling)
                        i += o(t)
                } else if (3 === r || 4 === r)
                    return t.nodeValue
            } else
                for (; e = t[n++]; )
                    i += o(e);
            return i
        }
        ,
        (w = ot.selectors = {
            cacheLength: 50,
            createPseudo: at,
            match: X,
            attrHandle: {},
            find: {},
            relative: {
                ">": {
                    dir: "parentNode",
                    first: !0
                },
                " ": {
                    dir: "parentNode"
                },
                "+": {
                    dir: "previousSibling",
                    first: !0
                },
                "~": {
                    dir: "previousSibling"
                }
            },
            preFilter: {
                ATTR: function(t) {
                    return t[1] = t[1].replace(J, tt),
                    t[3] = (t[3] || t[4] || t[5] || "").replace(J, tt),
                    "~=" === t[2] && (t[3] = " " + t[3] + " "),
                    t.slice(0, 4)
                },
                CHILD: function(t) {
                    return t[1] = t[1].toLowerCase(),
                    "nth" === t[1].slice(0, 3) ? (t[3] || ot.error(t[0]),
                    t[4] = +(t[4] ? t[5] + (t[6] || 1) : 2 * ("even" === t[3] || "odd" === t[3])),
                    t[5] = +(t[7] + t[8] || "odd" === t[3])) : t[3] && ot.error(t[0]),
                    t
                },
                PSEUDO: function(t) {
                    var e, i = !t[6] && t[2];
                    return X.CHILD.test(t[0]) ? null : (t[3] ? t[2] = t[4] || t[5] || "" : i && U.test(i) && (e = p(i, !0)) && (e = i.indexOf(")", i.length - e) - i.length) && (t[0] = t[0].slice(0, e),
                    t[2] = i.slice(0, e)),
                    t.slice(0, 3))
                }
            },
            filter: {
                TAG: function(t) {
                    var e = t.replace(J, tt).toLowerCase();
                    return "*" === t ? function() {
                        return !0
                    }
                    : function(t) {
                        return t.nodeName && t.nodeName.toLowerCase() === e
                    }
                },
                CLASS: function(t) {
                    var e = f[t + " "];
                    return e || (e = new RegExp("(^|" + z + ")" + t + "(" + z + "|$)")) && f(t, function(t) {
                        return e.test("string" == typeof t.className && t.className || void 0 !== t.getAttribute && t.getAttribute("class") || "")
                    })
                },
                ATTR: function(i, n, r) {
                    return function(t) {
                        var e = ot.attr(t, i);
                        return null == e ? "!=" === n : !n || (e += "",
                        "=" === n ? e === r : "!=" === n ? e !== r : "^=" === n ? r && 0 === e.indexOf(r) : "*=" === n ? r && -1 < e.indexOf(r) : "$=" === n ? r && e.slice(-r.length) === r : "~=" === n ? -1 < (" " + e.replace(B, " ") + " ").indexOf(r) : "|=" === n && (e === r || e.slice(0, r.length + 1) === r + "-"))
                    }
                },
                CHILD: function(p, t, e, g, _) {
                    var m = "nth" !== p.slice(0, 3)
                      , v = "last" !== p.slice(-4)
                      , y = "of-type" === t;
                    return 1 === g && 0 === _ ? function(t) {
                        return !!t.parentNode
                    }
                    : function(t, e, i) {
                        var n, r, o, s, a, l, u = m !== v ? "nextSibling" : "previousSibling", c = t.parentNode, h = y && t.nodeName.toLowerCase(), f = !i && !y, d = !1;
                        if (c) {
                            if (m) {
                                for (; u; ) {
                                    for (s = t; s = s[u]; )
                                        if (y ? s.nodeName.toLowerCase() === h : 1 === s.nodeType)
                                            return !1;
                                    l = u = "only" === p && !l && "nextSibling"
                                }
                                return !0
                            }
                            if (l = [v ? c.firstChild : c.lastChild],
                            v && f) {
                                for (d = (a = (n = (r = (o = (s = c)[k] || (s[k] = {}))[s.uniqueID] || (o[s.uniqueID] = {}))[p] || [])[0] === S && n[1]) && n[2],
                                s = a && c.childNodes[a]; s = ++a && s && s[u] || (d = a = 0) || l.pop(); )
                                    if (1 === s.nodeType && ++d && s === t) {
                                        r[p] = [S, a, d];
                                        break
                                    }
                            } else if (f && (d = a = (n = (r = (o = (s = t)[k] || (s[k] = {}))[s.uniqueID] || (o[s.uniqueID] = {}))[p] || [])[0] === S && n[1]),
                            !1 === d)
                                for (; (s = ++a && s && s[u] || (d = a = 0) || l.pop()) && ((y ? s.nodeName.toLowerCase() !== h : 1 !== s.nodeType) || !++d || (f && ((r = (o = s[k] || (s[k] = {}))[s.uniqueID] || (o[s.uniqueID] = {}))[p] = [S, d]),
                                s !== t)); )
                                    ;
                            return (d -= _) === g || d % g == 0 && 0 <= d / g
                        }
                    }
                },
                PSEUDO: function(t, o) {
                    var e, s = w.pseudos[t] || w.setFilters[t.toLowerCase()] || ot.error("unsupported pseudo: " + t);
                    return s[k] ? s(o) : 1 < s.length ? (e = [t, t, "", o],
                    w.setFilters.hasOwnProperty(t.toLowerCase()) ? at(function(t, e) {
                        for (var i, n = s(t, o), r = n.length; r--; )
                            t[i = M(t, n[r])] = !(e[i] = n[r])
                    }) : function(t) {
                        return s(t, 0, e)
                    }
                    ) : s
                }
            },
            pseudos: {
                not: at(function(t) {
                    var n = []
                      , r = []
                      , a = h(t.replace(L, "$1"));
                    return a[k] ? at(function(t, e, i, n) {
                        for (var r, o = a(t, null, n, []), s = t.length; s--; )
                            (r = o[s]) && (t[s] = !(e[s] = r))
                    }) : function(t, e, i) {
                        return n[0] = t,
                        a(n, null, i, r),
                        n[0] = null,
                        !r.pop()
                    }
                }),
                has: at(function(e) {
                    return function(t) {
                        return 0 < ot(e, t).length
                    }
                }),
                contains: at(function(e) {
                    return e = e.replace(J, tt),
                    function(t) {
                        return -1 < (t.textContent || t.innerText || o(t)).indexOf(e)
                    }
                }),
                lang: at(function(i) {
                    return V.test(i || "") || ot.error("unsupported lang: " + i),
                    i = i.replace(J, tt).toLowerCase(),
                    function(t) {
                        var e;
                        do {
                            if (e = C ? t.lang : t.getAttribute("xml:lang") || t.getAttribute("lang"))
                                return (e = e.toLowerCase()) === i || 0 === e.indexOf(i + "-")
                        } while ((t = t.parentNode) && 1 === t.nodeType);return !1
                    }
                }),
                target: function(t) {
                    var e = i.location && i.location.hash;
                    return e && e.slice(1) === t.id
                },
                root: function(t) {
                    return t === s
                },
                focus: function(t) {
                    return t === T.activeElement && (!T.hasFocus || T.hasFocus()) && !!(t.type || t.href || ~t.tabIndex)
                },
                enabled: dt(!1),
                disabled: dt(!0),
                checked: function(t) {
                    var e = t.nodeName.toLowerCase();
                    return "input" === e && !!t.checked || "option" === e && !!t.selected
                },
                selected: function(t) {
                    return t.parentNode && t.parentNode.selectedIndex,
                    !0 === t.selected
                },
                empty: function(t) {
                    for (t = t.firstChild; t; t = t.nextSibling)
                        if (t.nodeType < 6)
                            return !1;
                    return !0
                },
                parent: function(t) {
                    return !w.pseudos.empty(t)
                },
                header: function(t) {
                    return Y.test(t.nodeName)
                },
                input: function(t) {
                    return G.test(t.nodeName)
                },
                button: function(t) {
                    var e = t.nodeName.toLowerCase();
                    return "input" === e && "button" === t.type || "button" === e
                },
                text: function(t) {
                    var e;
                    return "input" === t.nodeName.toLowerCase() && "text" === t.type && (null == (e = t.getAttribute("type")) || "text" === e.toLowerCase())
                },
                first: pt(function() {
                    return [0]
                }),
                last: pt(function(t, e) {
                    return [e - 1]
                }),
                eq: pt(function(t, e, i) {
                    return [i < 0 ? i + e : i]
                }),
                even: pt(function(t, e) {
                    for (var i = 0; i < e; i += 2)
                        t.push(i);
                    return t
                }),
                odd: pt(function(t, e) {
                    for (var i = 1; i < e; i += 2)
                        t.push(i);
                    return t
                }),
                lt: pt(function(t, e, i) {
                    for (var n = i < 0 ? i + e : i; 0 <= --n; )
                        t.push(n);
                    return t
                }),
                gt: pt(function(t, e, i) {
                    for (var n = i < 0 ? i + e : i; ++n < e; )
                        t.push(n);
                    return t
                })
            }
        }).pseudos.nth = w.pseudos.eq,
        {
            radio: !0,
            checkbox: !0,
            file: !0,
            password: !0,
            image: !0
        })
            w.pseudos[t] = ht(t);
        for (t in {
            submit: !0,
            reset: !0
        })
            w.pseudos[t] = ft(t);
        function _t() {}
        function mt(t) {
            for (var e = 0, i = t.length, n = ""; e < i; e++)
                n += t[e].value;
            return n
        }
        function vt(a, t, e) {
            var l = t.dir
              , u = t.next
              , c = u || l
              , h = e && "parentNode" === c
              , f = n++;
            return t.first ? function(t, e, i) {
                for (; t = t[l]; )
                    if (1 === t.nodeType || h)
                        return a(t, e, i);
                return !1
            }
            : function(t, e, i) {
                var n, r, o, s = [S, f];
                if (i) {
                    for (; t = t[l]; )
                        if ((1 === t.nodeType || h) && a(t, e, i))
                            return !0
                } else
                    for (; t = t[l]; )
                        if (1 === t.nodeType || h)
                            if (r = (o = t[k] || (t[k] = {}))[t.uniqueID] || (o[t.uniqueID] = {}),
                            u && u === t.nodeName.toLowerCase())
                                t = t[l] || t;
                            else {
                                if ((n = r[c]) && n[0] === S && n[1] === f)
                                    return s[2] = n[2];
                                if ((r[c] = s)[2] = a(t, e, i))
                                    return !0
                            }
                return !1
            }
        }
        function yt(r) {
            return 1 < r.length ? function(t, e, i) {
                for (var n = r.length; n--; )
                    if (!r[n](t, e, i))
                        return !1;
                return !0
            }
            : r[0]
        }
        function wt(t, e, i, n, r) {
            for (var o, s = [], a = 0, l = t.length, u = null != e; a < l; a++)
                (o = t[a]) && (i && !i(o, n, r) || (s.push(o),
                u && e.push(a)));
            return s
        }
        function bt(d, p, g, _, m, t) {
            return _ && !_[k] && (_ = bt(_)),
            m && !m[k] && (m = bt(m, t)),
            at(function(t, e, i, n) {
                var r, o, s, a = [], l = [], u = e.length, c = t || function(t, e, i) {
                    for (var n = 0, r = e.length; n < r; n++)
                        ot(t, e[n], i);
                    return i
                }(p || "*", i.nodeType ? [i] : i, []), h = !d || !t && p ? c : wt(c, a, d, i, n), f = g ? m || (t ? d : u || _) ? [] : e : h;
                if (g && g(h, f, i, n),
                _)
                    for (r = wt(f, l),
                    _(r, [], i, n),
                    o = r.length; o--; )
                        (s = r[o]) && (f[l[o]] = !(h[l[o]] = s));
                if (t) {
                    if (m || d) {
                        if (m) {
                            for (r = [],
                            o = f.length; o--; )
                                (s = f[o]) && r.push(h[o] = s);
                            m(null, f = [], r, n)
                        }
                        for (o = f.length; o--; )
                            (s = f[o]) && -1 < (r = m ? M(t, s) : a[o]) && (t[r] = !(e[r] = s))
                    }
                } else
                    f = wt(f === e ? f.splice(u, f.length) : f),
                    m ? m(null, e, f, n) : O.apply(e, f)
            })
        }
        function xt(t) {
            for (var r, e, i, n = t.length, o = w.relative[t[0].type], s = o || w.relative[" "], a = o ? 1 : 0, l = vt(function(t) {
                return t === r
            }, s, !0), u = vt(function(t) {
                return -1 < M(r, t)
            }, s, !0), c = [function(t, e, i) {
                var n = !o && (i || e !== b) || ((r = e).nodeType ? l(t, e, i) : u(t, e, i));
                return r = null,
                n
            }
            ]; a < n; a++)
                if (e = w.relative[t[a].type])
                    c = [vt(yt(c), e)];
                else {
                    if ((e = w.filter[t[a].type].apply(null, t[a].matches))[k]) {
                        for (i = ++a; i < n && !w.relative[t[i].type]; i++)
                            ;
                        return bt(1 < a && yt(c), 1 < a && mt(t.slice(0, a - 1).concat({
                            value: " " === t[a - 2].type ? "*" : ""
                        })).replace(L, "$1"), e, a < i && xt(t.slice(a, i)), i < n && xt(t = t.slice(i)), i < n && mt(t))
                    }
                    c.push(e)
                }
            return yt(c)
        }
        return _t.prototype = w.filters = w.pseudos,
        w.setFilters = new _t,
        p = ot.tokenize = function(t, e) {
            var i, n, r, o, s, a, l, u = y[t + " "];
            if (u)
                return e ? 0 : u.slice(0);
            for (s = t,
            a = [],
            l = w.preFilter; s; ) {
                for (o in i && !(n = q.exec(s)) || (n && (s = s.slice(n[0].length) || s),
                a.push(r = [])),
                i = !1,
                (n = W.exec(s)) && (i = n.shift(),
                r.push({
                    value: i,
                    type: n[0].replace(L, " ")
                }),
                s = s.slice(i.length)),
                w.filter)
                    !(n = X[o].exec(s)) || l[o] && !(n = l[o](n)) || (i = n.shift(),
                    r.push({
                        value: i,
                        type: o,
                        matches: n
                    }),
                    s = s.slice(i.length));
                if (!i)
                    break
            }
            return e ? s.length : s ? ot.error(t) : y(t, a).slice(0)
        }
        ,
        h = ot.compile = function(t, e) {
            var i, _, m, v, y, n, r = [], o = [], s = $[t + " "];
            if (!s) {
                for (e || (e = p(t)),
                i = e.length; i--; )
                    (s = xt(e[i]))[k] ? r.push(s) : o.push(s);
                (s = $(t, (_ = o,
                m = r,
                v = 0 < m.length,
                y = 0 < _.length,
                n = function(t, e, i, n, r) {
                    var o, s, a, l = 0, u = "0", c = t && [], h = [], f = b, d = t || y && w.find.TAG("*", r), p = S += null == f ? 1 : Math.random() || .1, g = d.length;
                    for (r && (b = e === T || e || r); u !== g && null != (o = d[u]); u++) {
                        if (y && o) {
                            for (s = 0,
                            e || o.ownerDocument === T || (x(o),
                            i = !C); a = _[s++]; )
                                if (a(o, e || T, i)) {
                                    n.push(o);
                                    break
                                }
                            r && (S = p)
                        }
                        v && ((o = !a && o) && l--,
                        t && c.push(o))
                    }
                    if (l += u,
                    v && u !== l) {
                        for (s = 0; a = m[s++]; )
                            a(c, h, e, i);
                        if (t) {
                            if (0 < l)
                                for (; u--; )
                                    c[u] || h[u] || (h[u] = E.call(n));
                            h = wt(h)
                        }
                        O.apply(n, h),
                        r && !t && 0 < h.length && 1 < l + m.length && ot.uniqueSort(n)
                    }
                    return r && (S = p,
                    b = f),
                    c
                }
                ,
                v ? at(n) : n))).selector = t
            }
            return s
        }
        ,
        g = ot.select = function(t, e, i, n) {
            var r, o, s, a, l, u = "function" == typeof t && t, c = !n && p(t = u.selector || t);
            if (i = i || [],
            1 === c.length) {
                if (2 < (o = c[0] = c[0].slice(0)).length && "ID" === (s = o[0]).type && 9 === e.nodeType && C && w.relative[o[1].type]) {
                    if (!(e = (w.find.ID(s.matches[0].replace(J, tt), e) || [])[0]))
                        return i;
                    u && (e = e.parentNode),
                    t = t.slice(o.shift().value.length)
                }
                for (r = X.needsContext.test(t) ? 0 : o.length; r-- && (s = o[r],
                !w.relative[a = s.type]); )
                    if ((l = w.find[a]) && (n = l(s.matches[0].replace(J, tt), K.test(o[0].type) && gt(e.parentNode) || e))) {
                        if (o.splice(r, 1),
                        !(t = n.length && mt(o)))
                            return O.apply(i, n),
                            i;
                        break
                    }
            }
            return (u || h(t, c))(n, e, !C, i, !e || K.test(t) && gt(e.parentNode) || e),
            i
        }
        ,
        d.sortStable = k.split("").sort(D).join("") === k,
        d.detectDuplicates = !!u,
        x(),
        d.sortDetached = lt(function(t) {
            return 1 & t.compareDocumentPosition(T.createElement("fieldset"))
        }),
        lt(function(t) {
            return t.innerHTML = "<a href='#'></a>",
            "#" === t.firstChild.getAttribute("href")
        }) || ut("type|href|height|width", function(t, e, i) {
            if (!i)
                return t.getAttribute(e, "type" === e.toLowerCase() ? 1 : 2)
        }),
        d.attributes && lt(function(t) {
            return t.innerHTML = "<input/>",
            t.firstChild.setAttribute("value", ""),
            "" === t.firstChild.getAttribute("value")
        }) || ut("value", function(t, e, i) {
            if (!i && "input" === t.nodeName.toLowerCase())
                return t.defaultValue
        }),
        lt(function(t) {
            return null == t.getAttribute("disabled")
        }) || ut(j, function(t, e, i) {
            var n;
            if (!i)
                return !0 === t[e] ? e.toLowerCase() : (n = t.getAttributeNode(e)) && n.specified ? n.value : null
        }),
        ot
    }(T);
    k.find = d,
    k.expr = d.selectors,
    k.expr[":"] = k.expr.pseudos,
    k.uniqueSort = k.unique = d.uniqueSort,
    k.text = d.getText,
    k.isXMLDoc = d.isXML,
    k.contains = d.contains,
    k.escapeSelector = d.escape;
    var p = function(t, e, i) {
        for (var n = [], r = void 0 !== i; (t = t[e]) && 9 !== t.nodeType; )
            if (1 === t.nodeType) {
                if (r && k(t).is(i))
                    break;
                n.push(t)
            }
        return n
    }
      , x = function(t, e) {
        for (var i = []; t; t = t.nextSibling)
            1 === t.nodeType && t !== e && i.push(t);
        return i
    }
      , S = k.expr.match.needsContext;
    function $(t, e) {
        return t.nodeName && t.nodeName.toLowerCase() === e.toLowerCase()
    }
    var D = /^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i;
    function A(t, i, n) {
        return v(i) ? k.grep(t, function(t, e) {
            return !!i.call(t, e, t) !== n
        }) : i.nodeType ? k.grep(t, function(t) {
            return t === i !== n
        }) : "string" != typeof i ? k.grep(t, function(t) {
            return -1 < r.call(i, t) !== n
        }) : k.filter(i, t, n)
    }
    k.filter = function(t, e, i) {
        var n = e[0];
        return i && (t = ":not(" + t + ")"),
        1 === e.length && 1 === n.nodeType ? k.find.matchesSelector(n, t) ? [n] : [] : k.find.matches(t, k.grep(e, function(t) {
            return 1 === t.nodeType
        }))
    }
    ,
    k.fn.extend({
        find: function(t) {
            var e, i, n = this.length, r = this;
            if ("string" != typeof t)
                return this.pushStack(k(t).filter(function() {
                    for (e = 0; e < n; e++)
                        if (k.contains(r[e], this))
                            return !0
                }));
            for (i = this.pushStack([]),
            e = 0; e < n; e++)
                k.find(t, r[e], i);
            return 1 < n ? k.uniqueSort(i) : i
        },
        filter: function(t) {
            return this.pushStack(A(this, t || [], !1))
        },
        not: function(t) {
            return this.pushStack(A(this, t || [], !0))
        },
        is: function(t) {
            return !!A(this, "string" == typeof t && S.test(t) ? k(t) : t || [], !1).length
        }
    });
    var E, P = /^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/;
    (k.fn.init = function(t, e, i) {
        var n, r;
        if (!t)
            return this;
        if (i = i || E,
        "string" != typeof t)
            return t.nodeType ? (this[0] = t,
            this.length = 1,
            this) : v(t) ? void 0 !== i.ready ? i.ready(t) : t(k) : k.makeArray(t, this);
        if (!(n = "<" === t[0] && ">" === t[t.length - 1] && 3 <= t.length ? [null, t, null] : P.exec(t)) || !n[1] && e)
            return !e || e.jquery ? (e || i).find(t) : this.constructor(e).find(t);
        if (n[1]) {
            if (e = e instanceof k ? e[0] : e,
            k.merge(this, k.parseHTML(n[1], e && e.nodeType ? e.ownerDocument || e : C, !0)),
            D.test(n[1]) && k.isPlainObject(e))
                for (n in e)
                    v(this[n]) ? this[n](e[n]) : this.attr(n, e[n]);
            return this
        }
        return (r = C.getElementById(n[2])) && (this[0] = r,
        this.length = 1),
        this
    }
    ).prototype = k.fn,
    E = k(C);
    var O = /^(?:parents|prev(?:Until|All))/
      , R = {
        children: !0,
        contents: !0,
        next: !0,
        prev: !0
    };
    function M(t, e) {
        for (; (t = t[e]) && 1 !== t.nodeType; )
            ;
        return t
    }
    k.fn.extend({
        has: function(t) {
            var e = k(t, this)
              , i = e.length;
            return this.filter(function() {
                for (var t = 0; t < i; t++)
                    if (k.contains(this, e[t]))
                        return !0
            })
        },
        closest: function(t, e) {
            var i, n = 0, r = this.length, o = [], s = "string" != typeof t && k(t);
            if (!S.test(t))
                for (; n < r; n++)
                    for (i = this[n]; i && i !== e; i = i.parentNode)
                        if (i.nodeType < 11 && (s ? -1 < s.index(i) : 1 === i.nodeType && k.find.matchesSelector(i, t))) {
                            o.push(i);
                            break
                        }
            return this.pushStack(1 < o.length ? k.uniqueSort(o) : o)
        },
        index: function(t) {
            return t ? "string" == typeof t ? r.call(k(t), this[0]) : r.call(this, t.jquery ? t[0] : t) : this[0] && this[0].parentNode ? this.first().prevAll().length : -1
        },
        add: function(t, e) {
            return this.pushStack(k.uniqueSort(k.merge(this.get(), k(t, e))))
        },
        addBack: function(t) {
            return this.add(null == t ? this.prevObject : this.prevObject.filter(t))
        }
    }),
    k.each({
        parent: function(t) {
            var e = t.parentNode;
            return e && 11 !== e.nodeType ? e : null
        },
        parents: function(t) {
            return p(t, "parentNode")
        },
        parentsUntil: function(t, e, i) {
            return p(t, "parentNode", i)
        },
        next: function(t) {
            return M(t, "nextSibling")
        },
        prev: function(t) {
            return M(t, "previousSibling")
        },
        nextAll: function(t) {
            return p(t, "nextSibling")
        },
        prevAll: function(t) {
            return p(t, "previousSibling")
        },
        nextUntil: function(t, e, i) {
            return p(t, "nextSibling", i)
        },
        prevUntil: function(t, e, i) {
            return p(t, "previousSibling", i)
        },
        siblings: function(t) {
            return x((t.parentNode || {}).firstChild, t)
        },
        children: function(t) {
            return x(t.firstChild)
        },
        contents: function(t) {
            return $(t, "iframe") ? t.contentDocument : ($(t, "template") && (t = t.content || t),
            k.merge([], t.childNodes))
        }
    }, function(n, r) {
        k.fn[n] = function(t, e) {
            var i = k.map(this, r, t);
            return "Until" !== n.slice(-5) && (e = t),
            e && "string" == typeof e && (i = k.filter(e, i)),
            1 < this.length && (R[n] || k.uniqueSort(i),
            O.test(n) && i.reverse()),
            this.pushStack(i)
        }
    });
    var j = /[^\x20\t\r\n\f]+/g;
    function z(t) {
        return t
    }
    function F(t) {
        throw t
    }
    function I(t, e, i, n) {
        var r;
        try {
            t && v(r = t.promise) ? r.call(t).done(e).fail(i) : t && v(r = t.then) ? r.call(t, e, i) : e.apply(void 0, [t].slice(n))
        } catch (t) {
            i.apply(void 0, [t])
        }
    }
    k.Callbacks = function(n) {
        var t, i;
        n = "string" == typeof n ? (t = n,
        i = {},
        k.each(t.match(j) || [], function(t, e) {
            i[e] = !0
        }),
        i) : k.extend({}, n);
        var r, e, o, s, a = [], l = [], u = -1, c = function() {
            for (s = s || n.once,
            o = r = !0; l.length; u = -1)
                for (e = l.shift(); ++u < a.length; )
                    !1 === a[u].apply(e[0], e[1]) && n.stopOnFalse && (u = a.length,
                    e = !1);
            n.memory || (e = !1),
            r = !1,
            s && (a = e ? [] : "")
        }, h = {
            add: function() {
                return a && (e && !r && (u = a.length - 1,
                l.push(e)),
                function i(t) {
                    k.each(t, function(t, e) {
                        v(e) ? n.unique && h.has(e) || a.push(e) : e && e.length && "string" !== b(e) && i(e)
                    })
                }(arguments),
                e && !r && c()),
                this
            },
            remove: function() {
                return k.each(arguments, function(t, e) {
                    for (var i; -1 < (i = k.inArray(e, a, i)); )
                        a.splice(i, 1),
                        i <= u && u--
                }),
                this
            },
            has: function(t) {
                return t ? -1 < k.inArray(t, a) : 0 < a.length
            },
            empty: function() {
                return a && (a = []),
                this
            },
            disable: function() {
                return s = l = [],
                a = e = "",
                this
            },
            disabled: function() {
                return !a
            },
            lock: function() {
                return s = l = [],
                e || r || (a = e = ""),
                this
            },
            locked: function() {
                return !!s
            },
            fireWith: function(t, e) {
                return s || (e = [t, (e = e || []).slice ? e.slice() : e],
                l.push(e),
                r || c()),
                this
            },
            fire: function() {
                return h.fireWith(this, arguments),
                this
            },
            fired: function() {
                return !!o
            }
        };
        return h
    }
    ,
    k.extend({
        Deferred: function(t) {
            var o = [["notify", "progress", k.Callbacks("memory"), k.Callbacks("memory"), 2], ["resolve", "done", k.Callbacks("once memory"), k.Callbacks("once memory"), 0, "resolved"], ["reject", "fail", k.Callbacks("once memory"), k.Callbacks("once memory"), 1, "rejected"]]
              , r = "pending"
              , s = {
                state: function() {
                    return r
                },
                always: function() {
                    return a.done(arguments).fail(arguments),
                    this
                },
                catch: function(t) {
                    return s.then(null, t)
                },
                pipe: function() {
                    var r = arguments;
                    return k.Deferred(function(n) {
                        k.each(o, function(t, e) {
                            var i = v(r[e[4]]) && r[e[4]];
                            a[e[1]](function() {
                                var t = i && i.apply(this, arguments);
                                t && v(t.promise) ? t.promise().progress(n.notify).done(n.resolve).fail(n.reject) : n[e[0] + "With"](this, i ? [t] : arguments)
                            })
                        }),
                        r = null
                    }).promise()
                },
                then: function(e, i, n) {
                    var l = 0;
                    function u(r, o, s, a) {
                        return function() {
                            var i = this
                              , n = arguments
                              , t = function() {
                                var t, e;
                                if (!(r < l)) {
                                    if ((t = s.apply(i, n)) === o.promise())
                                        throw new TypeError("Thenable self-resolution");
                                    e = t && ("object" == typeof t || "function" == typeof t) && t.then,
                                    v(e) ? a ? e.call(t, u(l, o, z, a), u(l, o, F, a)) : (l++,
                                    e.call(t, u(l, o, z, a), u(l, o, F, a), u(l, o, z, o.notifyWith))) : (s !== z && (i = void 0,
                                    n = [t]),
                                    (a || o.resolveWith)(i, n))
                                }
                            }
                              , e = a ? t : function() {
                                try {
                                    t()
                                } catch (t) {
                                    k.Deferred.exceptionHook && k.Deferred.exceptionHook(t, e.stackTrace),
                                    l <= r + 1 && (s !== F && (i = void 0,
                                    n = [t]),
                                    o.rejectWith(i, n))
                                }
                            }
                            ;
                            r ? e() : (k.Deferred.getStackHook && (e.stackTrace = k.Deferred.getStackHook()),
                            T.setTimeout(e))
                        }
                    }
                    return k.Deferred(function(t) {
                        o[0][3].add(u(0, t, v(n) ? n : z, t.notifyWith)),
                        o[1][3].add(u(0, t, v(e) ? e : z)),
                        o[2][3].add(u(0, t, v(i) ? i : F))
                    }).promise()
                },
                promise: function(t) {
                    return null != t ? k.extend(t, s) : s
                }
            }
              , a = {};
            return k.each(o, function(t, e) {
                var i = e[2]
                  , n = e[5];
                s[e[1]] = i.add,
                n && i.add(function() {
                    r = n
                }, o[3 - t][2].disable, o[3 - t][3].disable, o[0][2].lock, o[0][3].lock),
                i.add(e[3].fire),
                a[e[0]] = function() {
                    return a[e[0] + "With"](this === a ? void 0 : this, arguments),
                    this
                }
                ,
                a[e[0] + "With"] = i.fireWith
            }),
            s.promise(a),
            t && t.call(a, a),
            a
        },
        when: function(t) {
            var i = arguments.length
              , e = i
              , n = Array(e)
              , r = a.call(arguments)
              , o = k.Deferred()
              , s = function(e) {
                return function(t) {
                    n[e] = this,
                    r[e] = 1 < arguments.length ? a.call(arguments) : t,
                    --i || o.resolveWith(n, r)
                }
            };
            if (i <= 1 && (I(t, o.done(s(e)).resolve, o.reject, !i),
            "pending" === o.state() || v(r[e] && r[e].then)))
                return o.then();
            for (; e--; )
                I(r[e], s(e), o.reject);
            return o.promise()
        }
    });
    var N = /^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;
    k.Deferred.exceptionHook = function(t, e) {
        T.console && T.console.warn && t && N.test(t.name) && T.console.warn("jQuery.Deferred exception: " + t.message, t.stack, e)
    }
    ,
    k.readyException = function(t) {
        T.setTimeout(function() {
            throw t
        })
    }
    ;
    var B = k.Deferred();
    function L() {
        C.removeEventListener("DOMContentLoaded", L),
        T.removeEventListener("load", L),
        k.ready()
    }
    k.fn.ready = function(t) {
        return B.then(t).catch(function(t) {
            k.readyException(t)
        }),
        this
    }
    ,
    k.extend({
        isReady: !1,
        readyWait: 1,
        ready: function(t) {
            (!0 === t ? --k.readyWait : k.isReady) || ((k.isReady = !0) !== t && 0 < --k.readyWait || B.resolveWith(C, [k]))
        }
    }),
    k.ready.then = B.then,
    "complete" === C.readyState || "loading" !== C.readyState && !C.documentElement.doScroll ? T.setTimeout(k.ready) : (C.addEventListener("DOMContentLoaded", L),
    T.addEventListener("load", L));
    var q = function(t, e, i, n, r, o, s) {
        var a = 0
          , l = t.length
          , u = null == i;
        if ("object" === b(i))
            for (a in r = !0,
            i)
                q(t, e, a, i[a], !0, o, s);
        else if (void 0 !== n && (r = !0,
        v(n) || (s = !0),
        u && (e = s ? (e.call(t, n),
        null) : (u = e,
        function(t, e, i) {
            return u.call(k(t), i)
        }
        )),
        e))
            for (; a < l; a++)
                e(t[a], i, s ? n : n.call(t[a], a, e(t[a], i)));
        return r ? t : u ? e.call(t) : l ? e(t[0], i) : o
    }
      , W = /^-ms-/
      , H = /-([a-z])/g;
    function U(t, e) {
        return e.toUpperCase()
    }
    function V(t) {
        return t.replace(W, "ms-").replace(H, U)
    }
    var X = function(t) {
        return 1 === t.nodeType || 9 === t.nodeType || !+t.nodeType
    };
    function G() {
        this.expando = k.expando + G.uid++
    }
    G.uid = 1,
    G.prototype = {
        cache: function(t) {
            var e = t[this.expando];
            return e || (e = {},
            X(t) && (t.nodeType ? t[this.expando] = e : Object.defineProperty(t, this.expando, {
                value: e,
                configurable: !0
            }))),
            e
        },
        set: function(t, e, i) {
            var n, r = this.cache(t);
            if ("string" == typeof e)
                r[V(e)] = i;
            else
                for (n in e)
                    r[V(n)] = e[n];
            return r
        },
        get: function(t, e) {
            return void 0 === e ? this.cache(t) : t[this.expando] && t[this.expando][V(e)]
        },
        access: function(t, e, i) {
            return void 0 === e || e && "string" == typeof e && void 0 === i ? this.get(t, e) : (this.set(t, e, i),
            void 0 !== i ? i : e)
        },
        remove: function(t, e) {
            var i, n = t[this.expando];
            if (void 0 !== n) {
                if (void 0 !== e) {
                    i = (e = Array.isArray(e) ? e.map(V) : (e = V(e))in n ? [e] : e.match(j) || []).length;
                    for (; i--; )
                        delete n[e[i]]
                }
                (void 0 === e || k.isEmptyObject(n)) && (t.nodeType ? t[this.expando] = void 0 : delete t[this.expando])
            }
        },
        hasData: function(t) {
            var e = t[this.expando];
            return void 0 !== e && !k.isEmptyObject(e)
        }
    };
    var Y = new G
      , Z = new G
      , Q = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/
      , K = /[A-Z]/g;
    function J(t, e, i) {
        var n, r;
        if (void 0 === i && 1 === t.nodeType)
            if (n = "data-" + e.replace(K, "-$&").toLowerCase(),
            "string" == typeof (i = t.getAttribute(n))) {
                try {
                    i = "true" === (r = i) || "false" !== r && ("null" === r ? null : r === +r + "" ? +r : Q.test(r) ? JSON.parse(r) : r)
                } catch (t) {}
                Z.set(t, e, i)
            } else
                i = void 0;
        return i
    }
    k.extend({
        hasData: function(t) {
            return Z.hasData(t) || Y.hasData(t)
        },
        data: function(t, e, i) {
            return Z.access(t, e, i)
        },
        removeData: function(t, e) {
            Z.remove(t, e)
        },
        _data: function(t, e, i) {
            return Y.access(t, e, i)
        },
        _removeData: function(t, e) {
            Y.remove(t, e)
        }
    }),
    k.fn.extend({
        data: function(i, t) {
            var e, n, r, o = this[0], s = o && o.attributes;
            if (void 0 !== i)
                return "object" == typeof i ? this.each(function() {
                    Z.set(this, i)
                }) : q(this, function(t) {
                    var e;
                    if (o && void 0 === t) {
                        if (void 0 !== (e = Z.get(o, i)))
                            return e;
                        if (void 0 !== (e = J(o, i)))
                            return e
                    } else
                        this.each(function() {
                            Z.set(this, i, t)
                        })
                }, null, t, 1 < arguments.length, null, !0);
            if (this.length && (r = Z.get(o),
            1 === o.nodeType && !Y.get(o, "hasDataAttrs"))) {
                for (e = s.length; e--; )
                    s[e] && 0 === (n = s[e].name).indexOf("data-") && (n = V(n.slice(5)),
                    J(o, n, r[n]));
                Y.set(o, "hasDataAttrs", !0)
            }
            return r
        },
        removeData: function(t) {
            return this.each(function() {
                Z.remove(this, t)
            })
        }
    }),
    k.extend({
        queue: function(t, e, i) {
            var n;
            if (t)
                return e = (e || "fx") + "queue",
                n = Y.get(t, e),
                i && (!n || Array.isArray(i) ? n = Y.access(t, e, k.makeArray(i)) : n.push(i)),
                n || []
        },
        dequeue: function(t, e) {
            e = e || "fx";
            var i = k.queue(t, e)
              , n = i.length
              , r = i.shift()
              , o = k._queueHooks(t, e);
            "inprogress" === r && (r = i.shift(),
            n--),
            r && ("fx" === e && i.unshift("inprogress"),
            delete o.stop,
            r.call(t, function() {
                k.dequeue(t, e)
            }, o)),
            !n && o && o.empty.fire()
        },
        _queueHooks: function(t, e) {
            var i = e + "queueHooks";
            return Y.get(t, i) || Y.access(t, i, {
                empty: k.Callbacks("once memory").add(function() {
                    Y.remove(t, [e + "queue", i])
                })
            })
        }
    }),
    k.fn.extend({
        queue: function(e, i) {
            var t = 2;
            return "string" != typeof e && (i = e,
            e = "fx",
            t--),
            arguments.length < t ? k.queue(this[0], e) : void 0 === i ? this : this.each(function() {
                var t = k.queue(this, e, i);
                k._queueHooks(this, e),
                "fx" === e && "inprogress" !== t[0] && k.dequeue(this, e)
            })
        },
        dequeue: function(t) {
            return this.each(function() {
                k.dequeue(this, t)
            })
        },
        clearQueue: function(t) {
            return this.queue(t || "fx", [])
        },
        promise: function(t, e) {
            var i, n = 1, r = k.Deferred(), o = this, s = this.length, a = function() {
                --n || r.resolveWith(o, [o])
            };
            for ("string" != typeof t && (e = t,
            t = void 0),
            t = t || "fx"; s--; )
                (i = Y.get(o[s], t + "queueHooks")) && i.empty && (n++,
                i.empty.add(a));
            return a(),
            r.promise(e)
        }
    });
    var tt = /[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source
      , et = new RegExp("^(?:([+-])=|)(" + tt + ")([a-z%]*)$","i")
      , it = ["Top", "Right", "Bottom", "Left"]
      , nt = function(t, e) {
        return "none" === (t = e || t).style.display || "" === t.style.display && k.contains(t.ownerDocument, t) && "none" === k.css(t, "display")
    }
      , rt = function(t, e, i, n) {
        var r, o, s = {};
        for (o in e)
            s[o] = t.style[o],
            t.style[o] = e[o];
        for (o in r = i.apply(t, n || []),
        e)
            t.style[o] = s[o];
        return r
    };
    function ot(t, e, i, n) {
        var r, o, s = 20, a = n ? function() {
            return n.cur()
        }
        : function() {
            return k.css(t, e, "")
        }
        , l = a(), u = i && i[3] || (k.cssNumber[e] ? "" : "px"), c = (k.cssNumber[e] || "px" !== u && +l) && et.exec(k.css(t, e));
        if (c && c[3] !== u) {
            for (l /= 2,
            u = u || c[3],
            c = +l || 1; s--; )
                k.style(t, e, c + u),
                (1 - o) * (1 - (o = a() / l || .5)) <= 0 && (s = 0),
                c /= o;
            c *= 2,
            k.style(t, e, c + u),
            i = i || []
        }
        return i && (c = +c || +l || 0,
        r = i[1] ? c + (i[1] + 1) * i[2] : +i[2],
        n && (n.unit = u,
        n.start = c,
        n.end = r)),
        r
    }
    var st = {};
    function at(t, e) {
        for (var i, n, r = [], o = 0, s = t.length; o < s; o++)
            (n = t[o]).style && (i = n.style.display,
            e ? ("none" === i && (r[o] = Y.get(n, "display") || null,
            r[o] || (n.style.display = "")),
            "" === n.style.display && nt(n) && (r[o] = (h = u = l = void 0,
            u = (a = n).ownerDocument,
            c = a.nodeName,
            (h = st[c]) || (l = u.body.appendChild(u.createElement(c)),
            h = k.css(l, "display"),
            l.parentNode.removeChild(l),
            "none" === h && (h = "block"),
            st[c] = h)))) : "none" !== i && (r[o] = "none",
            Y.set(n, "display", i)));
        var a, l, u, c, h;
        for (o = 0; o < s; o++)
            null != r[o] && (t[o].style.display = r[o]);
        return t
    }
    k.fn.extend({
        show: function() {
            return at(this, !0)
        },
        hide: function() {
            return at(this)
        },
        toggle: function(t) {
            return "boolean" == typeof t ? t ? this.show() : this.hide() : this.each(function() {
                nt(this) ? k(this).show() : k(this).hide()
            })
        }
    });
    var lt = /^(?:checkbox|radio)$/i
      , ut = /<([a-z][^\/\0>\x20\t\r\n\f]+)/i
      , ct = /^$|^module$|\/(?:java|ecma)script/i
      , ht = {
        option: [1, "<select multiple='multiple'>", "</select>"],
        thead: [1, "<table>", "</table>"],
        col: [2, "<table><colgroup>", "</colgroup></table>"],
        tr: [2, "<table><tbody>", "</tbody></table>"],
        td: [3, "<table><tbody><tr>", "</tr></tbody></table>"],
        _default: [0, "", ""]
    };
    function ft(t, e) {
        var i;
        return i = void 0 !== t.getElementsByTagName ? t.getElementsByTagName(e || "*") : void 0 !== t.querySelectorAll ? t.querySelectorAll(e || "*") : [],
        void 0 === e || e && $(t, e) ? k.merge([t], i) : i
    }
    function dt(t, e) {
        for (var i = 0, n = t.length; i < n; i++)
            Y.set(t[i], "globalEval", !e || Y.get(e[i], "globalEval"))
    }
    ht.optgroup = ht.option,
    ht.tbody = ht.tfoot = ht.colgroup = ht.caption = ht.thead,
    ht.th = ht.td;
    var pt, gt, _t = /<|&#?\w+;/;
    function mt(t, e, i, n, r) {
        for (var o, s, a, l, u, c, h = e.createDocumentFragment(), f = [], d = 0, p = t.length; d < p; d++)
            if ((o = t[d]) || 0 === o)
                if ("object" === b(o))
                    k.merge(f, o.nodeType ? [o] : o);
                else if (_t.test(o)) {
                    for (s = s || h.appendChild(e.createElement("div")),
                    a = (ut.exec(o) || ["", ""])[1].toLowerCase(),
                    l = ht[a] || ht._default,
                    s.innerHTML = l[1] + k.htmlPrefilter(o) + l[2],
                    c = l[0]; c--; )
                        s = s.lastChild;
                    k.merge(f, s.childNodes),
                    (s = h.firstChild).textContent = ""
                } else
                    f.push(e.createTextNode(o));
        for (h.textContent = "",
        d = 0; o = f[d++]; )
            if (n && -1 < k.inArray(o, n))
                r && r.push(o);
            else if (u = k.contains(o.ownerDocument, o),
            s = ft(h.appendChild(o), "script"),
            u && dt(s),
            i)
                for (c = 0; o = s[c++]; )
                    ct.test(o.type || "") && i.push(o);
        return h
    }
    pt = C.createDocumentFragment().appendChild(C.createElement("div")),
    (gt = C.createElement("input")).setAttribute("type", "radio"),
    gt.setAttribute("checked", "checked"),
    gt.setAttribute("name", "t"),
    pt.appendChild(gt),
    m.checkClone = pt.cloneNode(!0).cloneNode(!0).lastChild.checked,
    pt.innerHTML = "<textarea>x</textarea>",
    m.noCloneChecked = !!pt.cloneNode(!0).lastChild.defaultValue;
    var vt = C.documentElement
      , yt = /^key/
      , wt = /^(?:mouse|pointer|contextmenu|drag|drop)|click/
      , bt = /^([^.]*)(?:\.(.+)|)/;
    function xt() {
        return !0
    }
    function Tt() {
        return !1
    }
    function Ct() {
        try {
            return C.activeElement
        } catch (t) {}
    }
    function kt(t, e, i, n, r, o) {
        var s, a;
        if ("object" == typeof e) {
            for (a in "string" != typeof i && (n = n || i,
            i = void 0),
            e)
                kt(t, a, i, n, e[a], o);
            return t
        }
        if (null == n && null == r ? (r = i,
        n = i = void 0) : null == r && ("string" == typeof i ? (r = n,
        n = void 0) : (r = n,
        n = i,
        i = void 0)),
        !1 === r)
            r = Tt;
        else if (!r)
            return t;
        return 1 === o && (s = r,
        (r = function(t) {
            return k().off(t),
            s.apply(this, arguments)
        }
        ).guid = s.guid || (s.guid = k.guid++)),
        t.each(function() {
            k.event.add(this, e, r, n, i)
        })
    }
    k.event = {
        global: {},
        add: function(e, t, i, n, r) {
            var o, s, a, l, u, c, h, f, d, p, g, _ = Y.get(e);
            if (_)
                for (i.handler && (i = (o = i).handler,
                r = o.selector),
                r && k.find.matchesSelector(vt, r),
                i.guid || (i.guid = k.guid++),
                (l = _.events) || (l = _.events = {}),
                (s = _.handle) || (s = _.handle = function(t) {
                    return void 0 !== k && k.event.triggered !== t.type ? k.event.dispatch.apply(e, arguments) : void 0
                }
                ),
                u = (t = (t || "").match(j) || [""]).length; u--; )
                    d = g = (a = bt.exec(t[u]) || [])[1],
                    p = (a[2] || "").split(".").sort(),
                    d && (h = k.event.special[d] || {},
                    d = (r ? h.delegateType : h.bindType) || d,
                    h = k.event.special[d] || {},
                    c = k.extend({
                        type: d,
                        origType: g,
                        data: n,
                        handler: i,
                        guid: i.guid,
                        selector: r,
                        needsContext: r && k.expr.match.needsContext.test(r),
                        namespace: p.join(".")
                    }, o),
                    (f = l[d]) || ((f = l[d] = []).delegateCount = 0,
                    h.setup && !1 !== h.setup.call(e, n, p, s) || e.addEventListener && e.addEventListener(d, s)),
                    h.add && (h.add.call(e, c),
                    c.handler.guid || (c.handler.guid = i.guid)),
                    r ? f.splice(f.delegateCount++, 0, c) : f.push(c),
                    k.event.global[d] = !0)
        },
        remove: function(t, e, i, n, r) {
            var o, s, a, l, u, c, h, f, d, p, g, _ = Y.hasData(t) && Y.get(t);
            if (_ && (l = _.events)) {
                for (u = (e = (e || "").match(j) || [""]).length; u--; )
                    if (d = g = (a = bt.exec(e[u]) || [])[1],
                    p = (a[2] || "").split(".").sort(),
                    d) {
                        for (h = k.event.special[d] || {},
                        f = l[d = (n ? h.delegateType : h.bindType) || d] || [],
                        a = a[2] && new RegExp("(^|\\.)" + p.join("\\.(?:.*\\.|)") + "(\\.|$)"),
                        s = o = f.length; o--; )
                            c = f[o],
                            !r && g !== c.origType || i && i.guid !== c.guid || a && !a.test(c.namespace) || n && n !== c.selector && ("**" !== n || !c.selector) || (f.splice(o, 1),
                            c.selector && f.delegateCount--,
                            h.remove && h.remove.call(t, c));
                        s && !f.length && (h.teardown && !1 !== h.teardown.call(t, p, _.handle) || k.removeEvent(t, d, _.handle),
                        delete l[d])
                    } else
                        for (d in l)
                            k.event.remove(t, d + e[u], i, n, !0);
                k.isEmptyObject(l) && Y.remove(t, "handle events")
            }
        },
        dispatch: function(t) {
            var e, i, n, r, o, s, a = k.event.fix(t), l = new Array(arguments.length), u = (Y.get(this, "events") || {})[a.type] || [], c = k.event.special[a.type] || {};
            for (l[0] = a,
            e = 1; e < arguments.length; e++)
                l[e] = arguments[e];
            if (a.delegateTarget = this,
            !c.preDispatch || !1 !== c.preDispatch.call(this, a)) {
                for (s = k.event.handlers.call(this, a, u),
                e = 0; (r = s[e++]) && !a.isPropagationStopped(); )
                    for (a.currentTarget = r.elem,
                    i = 0; (o = r.handlers[i++]) && !a.isImmediatePropagationStopped(); )
                        a.rnamespace && !a.rnamespace.test(o.namespace) || (a.handleObj = o,
                        a.data = o.data,
                        void 0 !== (n = ((k.event.special[o.origType] || {}).handle || o.handler).apply(r.elem, l)) && !1 === (a.result = n) && (a.preventDefault(),
                        a.stopPropagation()));
                return c.postDispatch && c.postDispatch.call(this, a),
                a.result
            }
        },
        handlers: function(t, e) {
            var i, n, r, o, s, a = [], l = e.delegateCount, u = t.target;
            if (l && u.nodeType && !("click" === t.type && 1 <= t.button))
                for (; u !== this; u = u.parentNode || this)
                    if (1 === u.nodeType && ("click" !== t.type || !0 !== u.disabled)) {
                        for (o = [],
                        s = {},
                        i = 0; i < l; i++)
                            void 0 === s[r = (n = e[i]).selector + " "] && (s[r] = n.needsContext ? -1 < k(r, this).index(u) : k.find(r, this, null, [u]).length),
                            s[r] && o.push(n);
                        o.length && a.push({
                            elem: u,
                            handlers: o
                        })
                    }
            return u = this,
            l < e.length && a.push({
                elem: u,
                handlers: e.slice(l)
            }),
            a
        },
        addProp: function(e, t) {
            Object.defineProperty(k.Event.prototype, e, {
                enumerable: !0,
                configurable: !0,
                get: v(t) ? function() {
                    if (this.originalEvent)
                        return t(this.originalEvent)
                }
                : function() {
                    if (this.originalEvent)
                        return this.originalEvent[e]
                }
                ,
                set: function(t) {
                    Object.defineProperty(this, e, {
                        enumerable: !0,
                        configurable: !0,
                        writable: !0,
                        value: t
                    })
                }
            })
        },
        fix: function(t) {
            return t[k.expando] ? t : new k.Event(t)
        },
        special: {
            load: {
                noBubble: !0
            },
            focus: {
                trigger: function() {
                    if (this !== Ct() && this.focus)
                        return this.focus(),
                        !1
                },
                delegateType: "focusin"
            },
            blur: {
                trigger: function() {
                    if (this === Ct() && this.blur)
                        return this.blur(),
                        !1
                },
                delegateType: "focusout"
            },
            click: {
                trigger: function() {
                    if ("checkbox" === this.type && this.click && $(this, "input"))
                        return this.click(),
                        !1
                },
                _default: function(t) {
                    return $(t.target, "a")
                }
            },
            beforeunload: {
                postDispatch: function(t) {
                    void 0 !== t.result && t.originalEvent && (t.originalEvent.returnValue = t.result)
                }
            }
        }
    },
    k.removeEvent = function(t, e, i) {
        t.removeEventListener && t.removeEventListener(e, i)
    }
    ,
    k.Event = function(t, e) {
        if (!(this instanceof k.Event))
            return new k.Event(t,e);
        t && t.type ? (this.originalEvent = t,
        this.type = t.type,
        this.isDefaultPrevented = t.defaultPrevented || void 0 === t.defaultPrevented && !1 === t.returnValue ? xt : Tt,
        this.target = t.target && 3 === t.target.nodeType ? t.target.parentNode : t.target,
        this.currentTarget = t.currentTarget,
        this.relatedTarget = t.relatedTarget) : this.type = t,
        e && k.extend(this, e),
        this.timeStamp = t && t.timeStamp || Date.now(),
        this[k.expando] = !0
    }
    ,
    k.Event.prototype = {
        constructor: k.Event,
        isDefaultPrevented: Tt,
        isPropagationStopped: Tt,
        isImmediatePropagationStopped: Tt,
        isSimulated: !1,
        preventDefault: function() {
            var t = this.originalEvent;
            this.isDefaultPrevented = xt,
            t && !this.isSimulated && t.preventDefault()
        },
        stopPropagation: function() {
            var t = this.originalEvent;
            this.isPropagationStopped = xt,
            t && !this.isSimulated && t.stopPropagation()
        },
        stopImmediatePropagation: function() {
            var t = this.originalEvent;
            this.isImmediatePropagationStopped = xt,
            t && !this.isSimulated && t.stopImmediatePropagation(),
            this.stopPropagation()
        }
    },
    k.each({
        altKey: !0,
        bubbles: !0,
        cancelable: !0,
        changedTouches: !0,
        ctrlKey: !0,
        detail: !0,
        eventPhase: !0,
        metaKey: !0,
        pageX: !0,
        pageY: !0,
        shiftKey: !0,
        view: !0,
        char: !0,
        charCode: !0,
        key: !0,
        keyCode: !0,
        button: !0,
        buttons: !0,
        clientX: !0,
        clientY: !0,
        offsetX: !0,
        offsetY: !0,
        pointerId: !0,
        pointerType: !0,
        screenX: !0,
        screenY: !0,
        targetTouches: !0,
        toElement: !0,
        touches: !0,
        which: function(t) {
            var e = t.button;
            return null == t.which && yt.test(t.type) ? null != t.charCode ? t.charCode : t.keyCode : !t.which && void 0 !== e && wt.test(t.type) ? 1 & e ? 1 : 2 & e ? 3 : 4 & e ? 2 : 0 : t.which
        }
    }, k.event.addProp),
    k.each({
        mouseenter: "mouseover",
        mouseleave: "mouseout",
        pointerenter: "pointerover",
        pointerleave: "pointerout"
    }, function(t, r) {
        k.event.special[t] = {
            delegateType: r,
            bindType: r,
            handle: function(t) {
                var e, i = t.relatedTarget, n = t.handleObj;
                return i && (i === this || k.contains(this, i)) || (t.type = n.origType,
                e = n.handler.apply(this, arguments),
                t.type = r),
                e
            }
        }
    }),
    k.fn.extend({
        on: function(t, e, i, n) {
            return kt(this, t, e, i, n)
        },
        one: function(t, e, i, n) {
            return kt(this, t, e, i, n, 1)
        },
        off: function(t, e, i) {
            var n, r;
            if (t && t.preventDefault && t.handleObj)
                return n = t.handleObj,
                k(t.delegateTarget).off(n.namespace ? n.origType + "." + n.namespace : n.origType, n.selector, n.handler),
                this;
            if ("object" != typeof t)
                return !1 !== e && "function" != typeof e || (i = e,
                e = void 0),
                !1 === i && (i = Tt),
                this.each(function() {
                    k.event.remove(this, t, i, e)
                });
            for (r in t)
                this.off(r, e, t[r]);
            return this
        }
    });
    var St = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([a-z][^\/\0>\x20\t\r\n\f]*)[^>]*)\/>/gi
      , $t = /<script|<style|<link/i
      , Dt = /checked\s*(?:[^=]|=\s*.checked.)/i
      , At = /^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g;
    function Et(t, e) {
        return $(t, "table") && $(11 !== e.nodeType ? e : e.firstChild, "tr") && k(t).children("tbody")[0] || t
    }
    function Pt(t) {
        return t.type = (null !== t.getAttribute("type")) + "/" + t.type,
        t
    }
    function Ot(t) {
        return "true/" === (t.type || "").slice(0, 5) ? t.type = t.type.slice(5) : t.removeAttribute("type"),
        t
    }
    function Rt(t, e) {
        var i, n, r, o, s, a, l, u;
        if (1 === e.nodeType) {
            if (Y.hasData(t) && (o = Y.access(t),
            s = Y.set(e, o),
            u = o.events))
                for (r in delete s.handle,
                s.events = {},
                u)
                    for (i = 0,
                    n = u[r].length; i < n; i++)
                        k.event.add(e, r, u[r][i]);
            Z.hasData(t) && (a = Z.access(t),
            l = k.extend({}, a),
            Z.set(e, l))
        }
    }
    function Mt(i, n, r, o) {
        n = g.apply([], n);
        var t, e, s, a, l, u, c = 0, h = i.length, f = h - 1, d = n[0], p = v(d);
        if (p || 1 < h && "string" == typeof d && !m.checkClone && Dt.test(d))
            return i.each(function(t) {
                var e = i.eq(t);
                p && (n[0] = d.call(this, t, e.html())),
                Mt(e, n, r, o)
            });
        if (h && (e = (t = mt(n, i[0].ownerDocument, !1, i, o)).firstChild,
        1 === t.childNodes.length && (t = e),
        e || o)) {
            for (a = (s = k.map(ft(t, "script"), Pt)).length; c < h; c++)
                l = t,
                c !== f && (l = k.clone(l, !0, !0),
                a && k.merge(s, ft(l, "script"))),
                r.call(i[c], l, c);
            if (a)
                for (u = s[s.length - 1].ownerDocument,
                k.map(s, Ot),
                c = 0; c < a; c++)
                    l = s[c],
                    ct.test(l.type || "") && !Y.access(l, "globalEval") && k.contains(u, l) && (l.src && "module" !== (l.type || "").toLowerCase() ? k._evalUrl && k._evalUrl(l.src) : w(l.textContent.replace(At, ""), u, l))
        }
        return i
    }
    function jt(t, e, i) {
        for (var n, r = e ? k.filter(e, t) : t, o = 0; null != (n = r[o]); o++)
            i || 1 !== n.nodeType || k.cleanData(ft(n)),
            n.parentNode && (i && k.contains(n.ownerDocument, n) && dt(ft(n, "script")),
            n.parentNode.removeChild(n));
        return t
    }
    k.extend({
        htmlPrefilter: function(t) {
            return t.replace(St, "<$1></$2>")
        },
        clone: function(t, e, i) {
            var n, r, o, s, a, l, u, c = t.cloneNode(!0), h = k.contains(t.ownerDocument, t);
            if (!(m.noCloneChecked || 1 !== t.nodeType && 11 !== t.nodeType || k.isXMLDoc(t)))
                for (s = ft(c),
                n = 0,
                r = (o = ft(t)).length; n < r; n++)
                    a = o[n],
                    l = s[n],
                    void 0,
                    "input" === (u = l.nodeName.toLowerCase()) && lt.test(a.type) ? l.checked = a.checked : "input" !== u && "textarea" !== u || (l.defaultValue = a.defaultValue);
            if (e)
                if (i)
                    for (o = o || ft(t),
                    s = s || ft(c),
                    n = 0,
                    r = o.length; n < r; n++)
                        Rt(o[n], s[n]);
                else
                    Rt(t, c);
            return 0 < (s = ft(c, "script")).length && dt(s, !h && ft(t, "script")),
            c
        },
        cleanData: function(t) {
            for (var e, i, n, r = k.event.special, o = 0; void 0 !== (i = t[o]); o++)
                if (X(i)) {
                    if (e = i[Y.expando]) {
                        if (e.events)
                            for (n in e.events)
                                r[n] ? k.event.remove(i, n) : k.removeEvent(i, n, e.handle);
                        i[Y.expando] = void 0
                    }
                    i[Z.expando] && (i[Z.expando] = void 0)
                }
        }
    }),
    k.fn.extend({
        detach: function(t) {
            return jt(this, t, !0)
        },
        remove: function(t) {
            return jt(this, t)
        },
        text: function(t) {
            return q(this, function(t) {
                return void 0 === t ? k.text(this) : this.empty().each(function() {
                    1 !== this.nodeType && 11 !== this.nodeType && 9 !== this.nodeType || (this.textContent = t)
                })
            }, null, t, arguments.length)
        },
        append: function() {
            return Mt(this, arguments, function(t) {
                1 !== this.nodeType && 11 !== this.nodeType && 9 !== this.nodeType || Et(this, t).appendChild(t)
            })
        },
        prepend: function() {
            return Mt(this, arguments, function(t) {
                if (1 === this.nodeType || 11 === this.nodeType || 9 === this.nodeType) {
                    var e = Et(this, t);
                    e.insertBefore(t, e.firstChild)
                }
            })
        },
        before: function() {
            return Mt(this, arguments, function(t) {
                this.parentNode && this.parentNode.insertBefore(t, this)
            })
        },
        after: function() {
            return Mt(this, arguments, function(t) {
                this.parentNode && this.parentNode.insertBefore(t, this.nextSibling)
            })
        },
        empty: function() {
            for (var t, e = 0; null != (t = this[e]); e++)
                1 === t.nodeType && (k.cleanData(ft(t, !1)),
                t.textContent = "");
            return this
        },
        clone: function(t, e) {
            return t = null != t && t,
            e = null == e ? t : e,
            this.map(function() {
                return k.clone(this, t, e)
            })
        },
        html: function(t) {
            return q(this, function(t) {
                var e = this[0] || {}
                  , i = 0
                  , n = this.length;
                if (void 0 === t && 1 === e.nodeType)
                    return e.innerHTML;
                if ("string" == typeof t && !$t.test(t) && !ht[(ut.exec(t) || ["", ""])[1].toLowerCase()]) {
                    t = k.htmlPrefilter(t);
                    try {
                        for (; i < n; i++)
                            1 === (e = this[i] || {}).nodeType && (k.cleanData(ft(e, !1)),
                            e.innerHTML = t);
                        e = 0
                    } catch (t) {}
                }
                e && this.empty().append(t)
            }, null, t, arguments.length)
        },
        replaceWith: function() {
            var i = [];
            return Mt(this, arguments, function(t) {
                var e = this.parentNode;
                k.inArray(this, i) < 0 && (k.cleanData(ft(this)),
                e && e.replaceChild(t, this))
            }, i)
        }
    }),
    k.each({
        appendTo: "append",
        prependTo: "prepend",
        insertBefore: "before",
        insertAfter: "after",
        replaceAll: "replaceWith"
    }, function(t, s) {
        k.fn[t] = function(t) {
            for (var e, i = [], n = k(t), r = n.length - 1, o = 0; o <= r; o++)
                e = o === r ? this : this.clone(!0),
                k(n[o])[s](e),
                l.apply(i, e.get());
            return this.pushStack(i)
        }
    });
    var zt = new RegExp("^(" + tt + ")(?!px)[a-z%]+$","i")
      , Ft = function(t) {
        var e = t.ownerDocument.defaultView;
        return e && e.opener || (e = T),
        e.getComputedStyle(t)
    }
      , It = new RegExp(it.join("|"),"i");
    function Nt(t, e, i) {
        var n, r, o, s, a = t.style;
        return (i = i || Ft(t)) && ("" !== (s = i.getPropertyValue(e) || i[e]) || k.contains(t.ownerDocument, t) || (s = k.style(t, e)),
        !m.pixelBoxStyles() && zt.test(s) && It.test(e) && (n = a.width,
        r = a.minWidth,
        o = a.maxWidth,
        a.minWidth = a.maxWidth = a.width = s,
        s = i.width,
        a.width = n,
        a.minWidth = r,
        a.maxWidth = o)),
        void 0 !== s ? s + "" : s
    }
    function Bt(t, e) {
        return {
            get: function() {
                if (!t())
                    return (this.get = e).apply(this, arguments);
                delete this.get
            }
        }
    }
    !function() {
        function t() {
            if (l) {
                a.style.cssText = "position:absolute;left:-11111px;width:60px;margin-top:1px;padding:0;border:0",
                l.style.cssText = "position:relative;display:block;box-sizing:border-box;overflow:scroll;margin:auto;border:1px;padding:1px;width:60%;top:1%",
                vt.appendChild(a).appendChild(l);
                var t = T.getComputedStyle(l);
                i = "1%" !== t.top,
                s = 12 === e(t.marginLeft),
                l.style.right = "60%",
                o = 36 === e(t.right),
                n = 36 === e(t.width),
                l.style.position = "absolute",
                r = 36 === l.offsetWidth || "absolute",
                vt.removeChild(a),
                l = null
            }
        }
        function e(t) {
            return Math.round(parseFloat(t))
        }
        var i, n, r, o, s, a = C.createElement("div"), l = C.createElement("div");
        l.style && (l.style.backgroundClip = "content-box",
        l.cloneNode(!0).style.backgroundClip = "",
        m.clearCloneStyle = "content-box" === l.style.backgroundClip,
        k.extend(m, {
            boxSizingReliable: function() {
                return t(),
                n
            },
            pixelBoxStyles: function() {
                return t(),
                o
            },
            pixelPosition: function() {
                return t(),
                i
            },
            reliableMarginLeft: function() {
                return t(),
                s
            },
            scrollboxSize: function() {
                return t(),
                r
            }
        }))
    }();
    var Lt = /^(none|table(?!-c[ea]).+)/
      , qt = /^--/
      , Wt = {
        position: "absolute",
        visibility: "hidden",
        display: "block"
    }
      , Ht = {
        letterSpacing: "0",
        fontWeight: "400"
    }
      , Ut = ["Webkit", "Moz", "ms"]
      , Vt = C.createElement("div").style;
    function Xt(t) {
        var e = k.cssProps[t];
        return e || (e = k.cssProps[t] = function(t) {
            if (t in Vt)
                return t;
            for (var e = t[0].toUpperCase() + t.slice(1), i = Ut.length; i--; )
                if ((t = Ut[i] + e)in Vt)
                    return t
        }(t) || t),
        e
    }
    function Gt(t, e, i) {
        var n = et.exec(e);
        return n ? Math.max(0, n[2] - (i || 0)) + (n[3] || "px") : e
    }
    function Yt(t, e, i, n, r, o) {
        var s = "width" === e ? 1 : 0
          , a = 0
          , l = 0;
        if (i === (n ? "border" : "content"))
            return 0;
        for (; s < 4; s += 2)
            "margin" === i && (l += k.css(t, i + it[s], !0, r)),
            n ? ("content" === i && (l -= k.css(t, "padding" + it[s], !0, r)),
            "margin" !== i && (l -= k.css(t, "border" + it[s] + "Width", !0, r))) : (l += k.css(t, "padding" + it[s], !0, r),
            "padding" !== i ? l += k.css(t, "border" + it[s] + "Width", !0, r) : a += k.css(t, "border" + it[s] + "Width", !0, r));
        return !n && 0 <= o && (l += Math.max(0, Math.ceil(t["offset" + e[0].toUpperCase() + e.slice(1)] - o - l - a - .5))),
        l
    }
    function Zt(t, e, i) {
        var n = Ft(t)
          , r = Nt(t, e, n)
          , o = "border-box" === k.css(t, "boxSizing", !1, n)
          , s = o;
        if (zt.test(r)) {
            if (!i)
                return r;
            r = "auto"
        }
        return s = s && (m.boxSizingReliable() || r === t.style[e]),
        ("auto" === r || !parseFloat(r) && "inline" === k.css(t, "display", !1, n)) && (r = t["offset" + e[0].toUpperCase() + e.slice(1)],
        s = !0),
        (r = parseFloat(r) || 0) + Yt(t, e, i || (o ? "border" : "content"), s, n, r) + "px"
    }
    function Qt(t, e, i, n, r) {
        return new Qt.prototype.init(t,e,i,n,r)
    }
    k.extend({
        cssHooks: {
            opacity: {
                get: function(t, e) {
                    if (e) {
                        var i = Nt(t, "opacity");
                        return "" === i ? "1" : i
                    }
                }
            }
        },
        cssNumber: {
            animationIterationCount: !0,
            columnCount: !0,
            fillOpacity: !0,
            flexGrow: !0,
            flexShrink: !0,
            fontWeight: !0,
            lineHeight: !0,
            opacity: !0,
            order: !0,
            orphans: !0,
            widows: !0,
            zIndex: !0,
            zoom: !0
        },
        cssProps: {},
        style: function(t, e, i, n) {
            if (t && 3 !== t.nodeType && 8 !== t.nodeType && t.style) {
                var r, o, s, a = V(e), l = qt.test(e), u = t.style;
                if (l || (e = Xt(a)),
                s = k.cssHooks[e] || k.cssHooks[a],
                void 0 === i)
                    return s && "get"in s && void 0 !== (r = s.get(t, !1, n)) ? r : u[e];
                "string" == (o = typeof i) && (r = et.exec(i)) && r[1] && (i = ot(t, e, r),
                o = "number"),
                null != i && i == i && ("number" === o && (i += r && r[3] || (k.cssNumber[a] ? "" : "px")),
                m.clearCloneStyle || "" !== i || 0 !== e.indexOf("background") || (u[e] = "inherit"),
                s && "set"in s && void 0 === (i = s.set(t, i, n)) || (l ? u.setProperty(e, i) : u[e] = i))
            }
        },
        css: function(t, e, i, n) {
            var r, o, s, a = V(e);
            return qt.test(e) || (e = Xt(a)),
            (s = k.cssHooks[e] || k.cssHooks[a]) && "get"in s && (r = s.get(t, !0, i)),
            void 0 === r && (r = Nt(t, e, n)),
            "normal" === r && e in Ht && (r = Ht[e]),
            "" === i || i ? (o = parseFloat(r),
            !0 === i || isFinite(o) ? o || 0 : r) : r
        }
    }),
    k.each(["height", "width"], function(t, a) {
        k.cssHooks[a] = {
            get: function(t, e, i) {
                if (e)
                    return !Lt.test(k.css(t, "display")) || t.getClientRects().length && t.getBoundingClientRect().width ? Zt(t, a, i) : rt(t, Wt, function() {
                        return Zt(t, a, i)
                    })
            },
            set: function(t, e, i) {
                var n, r = Ft(t), o = "border-box" === k.css(t, "boxSizing", !1, r), s = i && Yt(t, a, i, o, r);
                return o && m.scrollboxSize() === r.position && (s -= Math.ceil(t["offset" + a[0].toUpperCase() + a.slice(1)] - parseFloat(r[a]) - Yt(t, a, "border", !1, r) - .5)),
                s && (n = et.exec(e)) && "px" !== (n[3] || "px") && (t.style[a] = e,
                e = k.css(t, a)),
                Gt(0, e, s)
            }
        }
    }),
    k.cssHooks.marginLeft = Bt(m.reliableMarginLeft, function(t, e) {
        if (e)
            return (parseFloat(Nt(t, "marginLeft")) || t.getBoundingClientRect().left - rt(t, {
                marginLeft: 0
            }, function() {
                return t.getBoundingClientRect().left
            })) + "px"
    }),
    k.each({
        margin: "",
        padding: "",
        border: "Width"
    }, function(r, o) {
        k.cssHooks[r + o] = {
            expand: function(t) {
                for (var e = 0, i = {}, n = "string" == typeof t ? t.split(" ") : [t]; e < 4; e++)
                    i[r + it[e] + o] = n[e] || n[e - 2] || n[0];
                return i
            }
        },
        "margin" !== r && (k.cssHooks[r + o].set = Gt)
    }),
    k.fn.extend({
        css: function(t, e) {
            return q(this, function(t, e, i) {
                var n, r, o = {}, s = 0;
                if (Array.isArray(e)) {
                    for (n = Ft(t),
                    r = e.length; s < r; s++)
                        o[e[s]] = k.css(t, e[s], !1, n);
                    return o
                }
                return void 0 !== i ? k.style(t, e, i) : k.css(t, e)
            }, t, e, 1 < arguments.length)
        }
    }),
    ((k.Tween = Qt).prototype = {
        constructor: Qt,
        init: function(t, e, i, n, r, o) {
            this.elem = t,
            this.prop = i,
            this.easing = r || k.easing._default,
            this.options = e,
            this.start = this.now = this.cur(),
            this.end = n,
            this.unit = o || (k.cssNumber[i] ? "" : "px")
        },
        cur: function() {
            var t = Qt.propHooks[this.prop];
            return t && t.get ? t.get(this) : Qt.propHooks._default.get(this)
        },
        run: function(t) {
            var e, i = Qt.propHooks[this.prop];
            return this.options.duration ? this.pos = e = k.easing[this.easing](t, this.options.duration * t, 0, 1, this.options.duration) : this.pos = e = t,
            this.now = (this.end - this.start) * e + this.start,
            this.options.step && this.options.step.call(this.elem, this.now, this),
            i && i.set ? i.set(this) : Qt.propHooks._default.set(this),
            this
        }
    }).init.prototype = Qt.prototype,
    (Qt.propHooks = {
        _default: {
            get: function(t) {
                var e;
                return 1 !== t.elem.nodeType || null != t.elem[t.prop] && null == t.elem.style[t.prop] ? t.elem[t.prop] : (e = k.css(t.elem, t.prop, "")) && "auto" !== e ? e : 0
            },
            set: function(t) {
                k.fx.step[t.prop] ? k.fx.step[t.prop](t) : 1 !== t.elem.nodeType || null == t.elem.style[k.cssProps[t.prop]] && !k.cssHooks[t.prop] ? t.elem[t.prop] = t.now : k.style(t.elem, t.prop, t.now + t.unit)
            }
        }
    }).scrollTop = Qt.propHooks.scrollLeft = {
        set: function(t) {
            t.elem.nodeType && t.elem.parentNode && (t.elem[t.prop] = t.now)
        }
    },
    k.easing = {
        linear: function(t) {
            return t
        },
        swing: function(t) {
            return .5 - Math.cos(t * Math.PI) / 2
        },
        _default: "swing"
    },
    k.fx = Qt.prototype.init,
    k.fx.step = {};
    var Kt, Jt, te, ee, ie = /^(?:toggle|show|hide)$/, ne = /queueHooks$/;
    function re() {
        Jt && (!1 === C.hidden && T.requestAnimationFrame ? T.requestAnimationFrame(re) : T.setTimeout(re, k.fx.interval),
        k.fx.tick())
    }
    function oe() {
        return T.setTimeout(function() {
            Kt = void 0
        }),
        Kt = Date.now()
    }
    function se(t, e) {
        var i, n = 0, r = {
            height: t
        };
        for (e = e ? 1 : 0; n < 4; n += 2 - e)
            r["margin" + (i = it[n])] = r["padding" + i] = t;
        return e && (r.opacity = r.width = t),
        r
    }
    function ae(t, e, i) {
        for (var n, r = (le.tweeners[e] || []).concat(le.tweeners["*"]), o = 0, s = r.length; o < s; o++)
            if (n = r[o].call(i, e, t))
                return n
    }
    function le(o, t, e) {
        var i, s, n = 0, r = le.prefilters.length, a = k.Deferred().always(function() {
            delete l.elem
        }), l = function() {
            if (s)
                return !1;
            for (var t = Kt || oe(), e = Math.max(0, u.startTime + u.duration - t), i = 1 - (e / u.duration || 0), n = 0, r = u.tweens.length; n < r; n++)
                u.tweens[n].run(i);
            return a.notifyWith(o, [u, i, e]),
            i < 1 && r ? e : (r || a.notifyWith(o, [u, 1, 0]),
            a.resolveWith(o, [u]),
            !1)
        }, u = a.promise({
            elem: o,
            props: k.extend({}, t),
            opts: k.extend(!0, {
                specialEasing: {},
                easing: k.easing._default
            }, e),
            originalProperties: t,
            originalOptions: e,
            startTime: Kt || oe(),
            duration: e.duration,
            tweens: [],
            createTween: function(t, e) {
                var i = k.Tween(o, u.opts, t, e, u.opts.specialEasing[t] || u.opts.easing);
                return u.tweens.push(i),
                i
            },
            stop: function(t) {
                var e = 0
                  , i = t ? u.tweens.length : 0;
                if (s)
                    return this;
                for (s = !0; e < i; e++)
                    u.tweens[e].run(1);
                return t ? (a.notifyWith(o, [u, 1, 0]),
                a.resolveWith(o, [u, t])) : a.rejectWith(o, [u, t]),
                this
            }
        }), c = u.props;
        for (function(t, e) {
            var i, n, r, o, s;
            for (i in t)
                if (r = e[n = V(i)],
                o = t[i],
                Array.isArray(o) && (r = o[1],
                o = t[i] = o[0]),
                i !== n && (t[n] = o,
                delete t[i]),
                (s = k.cssHooks[n]) && "expand"in s)
                    for (i in o = s.expand(o),
                    delete t[n],
                    o)
                        i in t || (t[i] = o[i],
                        e[i] = r);
                else
                    e[n] = r
        }(c, u.opts.specialEasing); n < r; n++)
            if (i = le.prefilters[n].call(u, o, c, u.opts))
                return v(i.stop) && (k._queueHooks(u.elem, u.opts.queue).stop = i.stop.bind(i)),
                i;
        return k.map(c, ae, u),
        v(u.opts.start) && u.opts.start.call(o, u),
        u.progress(u.opts.progress).done(u.opts.done, u.opts.complete).fail(u.opts.fail).always(u.opts.always),
        k.fx.timer(k.extend(l, {
            elem: o,
            anim: u,
            queue: u.opts.queue
        })),
        u
    }
    k.Animation = k.extend(le, {
        tweeners: {
            "*": [function(t, e) {
                var i = this.createTween(t, e);
                return ot(i.elem, t, et.exec(e), i),
                i
            }
            ]
        },
        tweener: function(t, e) {
            for (var i, n = 0, r = (t = v(t) ? (e = t,
            ["*"]) : t.match(j)).length; n < r; n++)
                i = t[n],
                le.tweeners[i] = le.tweeners[i] || [],
                le.tweeners[i].unshift(e)
        },
        prefilters: [function(t, e, i) {
            var n, r, o, s, a, l, u, c, h = "width"in e || "height"in e, f = this, d = {}, p = t.style, g = t.nodeType && nt(t), _ = Y.get(t, "fxshow");
            for (n in i.queue || (null == (s = k._queueHooks(t, "fx")).unqueued && (s.unqueued = 0,
            a = s.empty.fire,
            s.empty.fire = function() {
                s.unqueued || a()
            }
            ),
            s.unqueued++,
            f.always(function() {
                f.always(function() {
                    s.unqueued--,
                    k.queue(t, "fx").length || s.empty.fire()
                })
            })),
            e)
                if (r = e[n],
                ie.test(r)) {
                    if (delete e[n],
                    o = o || "toggle" === r,
                    r === (g ? "hide" : "show")) {
                        if ("show" !== r || !_ || void 0 === _[n])
                            continue;
                        g = !0
                    }
                    d[n] = _ && _[n] || k.style(t, n)
                }
            if ((l = !k.isEmptyObject(e)) || !k.isEmptyObject(d))
                for (n in h && 1 === t.nodeType && (i.overflow = [p.overflow, p.overflowX, p.overflowY],
                null == (u = _ && _.display) && (u = Y.get(t, "display")),
                "none" === (c = k.css(t, "display")) && (u ? c = u : (at([t], !0),
                u = t.style.display || u,
                c = k.css(t, "display"),
                at([t]))),
                ("inline" === c || "inline-block" === c && null != u) && "none" === k.css(t, "float") && (l || (f.done(function() {
                    p.display = u
                }),
                null == u && (c = p.display,
                u = "none" === c ? "" : c)),
                p.display = "inline-block")),
                i.overflow && (p.overflow = "hidden",
                f.always(function() {
                    p.overflow = i.overflow[0],
                    p.overflowX = i.overflow[1],
                    p.overflowY = i.overflow[2]
                })),
                l = !1,
                d)
                    l || (_ ? "hidden"in _ && (g = _.hidden) : _ = Y.access(t, "fxshow", {
                        display: u
                    }),
                    o && (_.hidden = !g),
                    g && at([t], !0),
                    f.done(function() {
                        for (n in g || at([t]),
                        Y.remove(t, "fxshow"),
                        d)
                            k.style(t, n, d[n])
                    })),
                    l = ae(g ? _[n] : 0, n, f),
                    n in _ || (_[n] = l.start,
                    g && (l.end = l.start,
                    l.start = 0))
        }
        ],
        prefilter: function(t, e) {
            e ? le.prefilters.unshift(t) : le.prefilters.push(t)
        }
    }),
    k.speed = function(t, e, i) {
        var n = t && "object" == typeof t ? k.extend({}, t) : {
            complete: i || !i && e || v(t) && t,
            duration: t,
            easing: i && e || e && !v(e) && e
        };
        return k.fx.off ? n.duration = 0 : "number" != typeof n.duration && (n.duration in k.fx.speeds ? n.duration = k.fx.speeds[n.duration] : n.duration = k.fx.speeds._default),
        null != n.queue && !0 !== n.queue || (n.queue = "fx"),
        n.old = n.complete,
        n.complete = function() {
            v(n.old) && n.old.call(this),
            n.queue && k.dequeue(this, n.queue)
        }
        ,
        n
    }
    ,
    k.fn.extend({
        fadeTo: function(t, e, i, n) {
            return this.filter(nt).css("opacity", 0).show().end().animate({
                opacity: e
            }, t, i, n)
        },
        animate: function(e, t, i, n) {
            var r = k.isEmptyObject(e)
              , o = k.speed(t, i, n)
              , s = function() {
                var t = le(this, k.extend({}, e), o);
                (r || Y.get(this, "finish")) && t.stop(!0)
            };
            return s.finish = s,
            r || !1 === o.queue ? this.each(s) : this.queue(o.queue, s)
        },
        stop: function(r, t, o) {
            var s = function(t) {
                var e = t.stop;
                delete t.stop,
                e(o)
            };
            return "string" != typeof r && (o = t,
            t = r,
            r = void 0),
            t && !1 !== r && this.queue(r || "fx", []),
            this.each(function() {
                var t = !0
                  , e = null != r && r + "queueHooks"
                  , i = k.timers
                  , n = Y.get(this);
                if (e)
                    n[e] && n[e].stop && s(n[e]);
                else
                    for (e in n)
                        n[e] && n[e].stop && ne.test(e) && s(n[e]);
                for (e = i.length; e--; )
                    i[e].elem !== this || null != r && i[e].queue !== r || (i[e].anim.stop(o),
                    t = !1,
                    i.splice(e, 1));
                !t && o || k.dequeue(this, r)
            })
        },
        finish: function(s) {
            return !1 !== s && (s = s || "fx"),
            this.each(function() {
                var t, e = Y.get(this), i = e[s + "queue"], n = e[s + "queueHooks"], r = k.timers, o = i ? i.length : 0;
                for (e.finish = !0,
                k.queue(this, s, []),
                n && n.stop && n.stop.call(this, !0),
                t = r.length; t--; )
                    r[t].elem === this && r[t].queue === s && (r[t].anim.stop(!0),
                    r.splice(t, 1));
                for (t = 0; t < o; t++)
                    i[t] && i[t].finish && i[t].finish.call(this);
                delete e.finish
            })
        }
    }),
    k.each(["toggle", "show", "hide"], function(t, n) {
        var r = k.fn[n];
        k.fn[n] = function(t, e, i) {
            return null == t || "boolean" == typeof t ? r.apply(this, arguments) : this.animate(se(n, !0), t, e, i)
        }
    }),
    k.each({
        slideDown: se("show"),
        slideUp: se("hide"),
        slideToggle: se("toggle"),
        fadeIn: {
            opacity: "show"
        },
        fadeOut: {
            opacity: "hide"
        },
        fadeToggle: {
            opacity: "toggle"
        }
    }, function(t, n) {
        k.fn[t] = function(t, e, i) {
            return this.animate(n, t, e, i)
        }
    }),
    k.timers = [],
    k.fx.tick = function() {
        var t, e = 0, i = k.timers;
        for (Kt = Date.now(); e < i.length; e++)
            (t = i[e])() || i[e] !== t || i.splice(e--, 1);
        i.length || k.fx.stop(),
        Kt = void 0
    }
    ,
    k.fx.timer = function(t) {
        k.timers.push(t),
        k.fx.start()
    }
    ,
    k.fx.interval = 13,
    k.fx.start = function() {
        Jt || (Jt = !0,
        re())
    }
    ,
    k.fx.stop = function() {
        Jt = null
    }
    ,
    k.fx.speeds = {
        slow: 600,
        fast: 200,
        _default: 400
    },
    k.fn.delay = function(n, t) {
        return n = k.fx && k.fx.speeds[n] || n,
        t = t || "fx",
        this.queue(t, function(t, e) {
            var i = T.setTimeout(t, n);
            e.stop = function() {
                T.clearTimeout(i)
            }
        })
    }
    ,
    te = C.createElement("input"),
    ee = C.createElement("select").appendChild(C.createElement("option")),
    te.type = "checkbox",
    m.checkOn = "" !== te.value,
    m.optSelected = ee.selected,
    (te = C.createElement("input")).value = "t",
    te.type = "radio",
    m.radioValue = "t" === te.value;
    var ue, ce = k.expr.attrHandle;
    k.fn.extend({
        attr: function(t, e) {
            return q(this, k.attr, t, e, 1 < arguments.length)
        },
        removeAttr: function(t) {
            return this.each(function() {
                k.removeAttr(this, t)
            })
        }
    }),
    k.extend({
        attr: function(t, e, i) {
            var n, r, o = t.nodeType;
            if (3 !== o && 8 !== o && 2 !== o)
                return void 0 === t.getAttribute ? k.prop(t, e, i) : (1 === o && k.isXMLDoc(t) || (r = k.attrHooks[e.toLowerCase()] || (k.expr.match.bool.test(e) ? ue : void 0)),
                void 0 !== i ? null === i ? void k.removeAttr(t, e) : r && "set"in r && void 0 !== (n = r.set(t, i, e)) ? n : (t.setAttribute(e, i + ""),
                i) : r && "get"in r && null !== (n = r.get(t, e)) ? n : null == (n = k.find.attr(t, e)) ? void 0 : n)
        },
        attrHooks: {
            type: {
                set: function(t, e) {
                    if (!m.radioValue && "radio" === e && $(t, "input")) {
                        var i = t.value;
                        return t.setAttribute("type", e),
                        i && (t.value = i),
                        e
                    }
                }
            }
        },
        removeAttr: function(t, e) {
            var i, n = 0, r = e && e.match(j);
            if (r && 1 === t.nodeType)
                for (; i = r[n++]; )
                    t.removeAttribute(i)
        }
    }),
    ue = {
        set: function(t, e, i) {
            return !1 === e ? k.removeAttr(t, i) : t.setAttribute(i, i),
            i
        }
    },
    k.each(k.expr.match.bool.source.match(/\w+/g), function(t, e) {
        var s = ce[e] || k.find.attr;
        ce[e] = function(t, e, i) {
            var n, r, o = e.toLowerCase();
            return i || (r = ce[o],
            ce[o] = n,
            n = null != s(t, e, i) ? o : null,
            ce[o] = r),
            n
        }
    });
    var he = /^(?:input|select|textarea|button)$/i
      , fe = /^(?:a|area)$/i;
    function de(t) {
        return (t.match(j) || []).join(" ")
    }
    function pe(t) {
        return t.getAttribute && t.getAttribute("class") || ""
    }
    function ge(t) {
        return Array.isArray(t) ? t : "string" == typeof t && t.match(j) || []
    }
    k.fn.extend({
        prop: function(t, e) {
            return q(this, k.prop, t, e, 1 < arguments.length)
        },
        removeProp: function(t) {
            return this.each(function() {
                delete this[k.propFix[t] || t]
            })
        }
    }),
    k.extend({
        prop: function(t, e, i) {
            var n, r, o = t.nodeType;
            if (3 !== o && 8 !== o && 2 !== o)
                return 1 === o && k.isXMLDoc(t) || (e = k.propFix[e] || e,
                r = k.propHooks[e]),
                void 0 !== i ? r && "set"in r && void 0 !== (n = r.set(t, i, e)) ? n : t[e] = i : r && "get"in r && null !== (n = r.get(t, e)) ? n : t[e]
        },
        propHooks: {
            tabIndex: {
                get: function(t) {
                    var e = k.find.attr(t, "tabindex");
                    return e ? parseInt(e, 10) : he.test(t.nodeName) || fe.test(t.nodeName) && t.href ? 0 : -1
                }
            }
        },
        propFix: {
            for: "htmlFor",
            class: "className"
        }
    }),
    m.optSelected || (k.propHooks.selected = {
        get: function(t) {
            var e = t.parentNode;
            return e && e.parentNode && e.parentNode.selectedIndex,
            null
        },
        set: function(t) {
            var e = t.parentNode;
            e && (e.selectedIndex,
            e.parentNode && e.parentNode.selectedIndex)
        }
    }),
    k.each(["tabIndex", "readOnly", "maxLength", "cellSpacing", "cellPadding", "rowSpan", "colSpan", "useMap", "frameBorder", "contentEditable"], function() {
        k.propFix[this.toLowerCase()] = this
    }),
    k.fn.extend({
        addClass: function(e) {
            var t, i, n, r, o, s, a, l = 0;
            if (v(e))
                return this.each(function(t) {
                    k(this).addClass(e.call(this, t, pe(this)))
                });
            if ((t = ge(e)).length)
                for (; i = this[l++]; )
                    if (r = pe(i),
                    n = 1 === i.nodeType && " " + de(r) + " ") {
                        for (s = 0; o = t[s++]; )
                            n.indexOf(" " + o + " ") < 0 && (n += o + " ");
                        r !== (a = de(n)) && i.setAttribute("class", a)
                    }
            return this
        },
        removeClass: function(e) {
            var t, i, n, r, o, s, a, l = 0;
            if (v(e))
                return this.each(function(t) {
                    k(this).removeClass(e.call(this, t, pe(this)))
                });
            if (!arguments.length)
                return this.attr("class", "");
            if ((t = ge(e)).length)
                for (; i = this[l++]; )
                    if (r = pe(i),
                    n = 1 === i.nodeType && " " + de(r) + " ") {
                        for (s = 0; o = t[s++]; )
                            for (; -1 < n.indexOf(" " + o + " "); )
                                n = n.replace(" " + o + " ", " ");
                        r !== (a = de(n)) && i.setAttribute("class", a)
                    }
            return this
        },
        toggleClass: function(r, e) {
            var o = typeof r
              , s = "string" === o || Array.isArray(r);
            return "boolean" == typeof e && s ? e ? this.addClass(r) : this.removeClass(r) : v(r) ? this.each(function(t) {
                k(this).toggleClass(r.call(this, t, pe(this), e), e)
            }) : this.each(function() {
                var t, e, i, n;
                if (s)
                    for (e = 0,
                    i = k(this),
                    n = ge(r); t = n[e++]; )
                        i.hasClass(t) ? i.removeClass(t) : i.addClass(t);
                else
                    void 0 !== r && "boolean" !== o || ((t = pe(this)) && Y.set(this, "__className__", t),
                    this.setAttribute && this.setAttribute("class", t || !1 === r ? "" : Y.get(this, "__className__") || ""))
            })
        },
        hasClass: function(t) {
            var e, i, n = 0;
            for (e = " " + t + " "; i = this[n++]; )
                if (1 === i.nodeType && -1 < (" " + de(pe(i)) + " ").indexOf(e))
                    return !0;
            return !1
        }
    });
    var _e = /\r/g;
    k.fn.extend({
        val: function(i) {
            var n, t, r, e = this[0];
            return arguments.length ? (r = v(i),
            this.each(function(t) {
                var e;
                1 === this.nodeType && (null == (e = r ? i.call(this, t, k(this).val()) : i) ? e = "" : "number" == typeof e ? e += "" : Array.isArray(e) && (e = k.map(e, function(t) {
                    return null == t ? "" : t + ""
                })),
                (n = k.valHooks[this.type] || k.valHooks[this.nodeName.toLowerCase()]) && "set"in n && void 0 !== n.set(this, e, "value") || (this.value = e))
            })) : e ? (n = k.valHooks[e.type] || k.valHooks[e.nodeName.toLowerCase()]) && "get"in n && void 0 !== (t = n.get(e, "value")) ? t : "string" == typeof (t = e.value) ? t.replace(_e, "") : null == t ? "" : t : void 0
        }
    }),
    k.extend({
        valHooks: {
            option: {
                get: function(t) {
                    var e = k.find.attr(t, "value");
                    return null != e ? e : de(k.text(t))
                }
            },
            select: {
                get: function(t) {
                    var e, i, n, r = t.options, o = t.selectedIndex, s = "select-one" === t.type, a = s ? null : [], l = s ? o + 1 : r.length;
                    for (n = o < 0 ? l : s ? o : 0; n < l; n++)
                        if (((i = r[n]).selected || n === o) && !i.disabled && (!i.parentNode.disabled || !$(i.parentNode, "optgroup"))) {
                            if (e = k(i).val(),
                            s)
                                return e;
                            a.push(e)
                        }
                    return a
                },
                set: function(t, e) {
                    for (var i, n, r = t.options, o = k.makeArray(e), s = r.length; s--; )
                        ((n = r[s]).selected = -1 < k.inArray(k.valHooks.option.get(n), o)) && (i = !0);
                    return i || (t.selectedIndex = -1),
                    o
                }
            }
        }
    }),
    k.each(["radio", "checkbox"], function() {
        k.valHooks[this] = {
            set: function(t, e) {
                if (Array.isArray(e))
                    return t.checked = -1 < k.inArray(k(t).val(), e)
            }
        },
        m.checkOn || (k.valHooks[this].get = function(t) {
            return null === t.getAttribute("value") ? "on" : t.value
        }
        )
    }),
    m.focusin = "onfocusin"in T;
    var me = /^(?:focusinfocus|focusoutblur)$/
      , ve = function(t) {
        t.stopPropagation()
    };
    k.extend(k.event, {
        trigger: function(t, e, i, n) {
            var r, o, s, a, l, u, c, h, f = [i || C], d = _.call(t, "type") ? t.type : t, p = _.call(t, "namespace") ? t.namespace.split(".") : [];
            if (o = h = s = i = i || C,
            3 !== i.nodeType && 8 !== i.nodeType && !me.test(d + k.event.triggered) && (-1 < d.indexOf(".") && (d = (p = d.split(".")).shift(),
            p.sort()),
            l = d.indexOf(":") < 0 && "on" + d,
            (t = t[k.expando] ? t : new k.Event(d,"object" == typeof t && t)).isTrigger = n ? 2 : 3,
            t.namespace = p.join("."),
            t.rnamespace = t.namespace ? new RegExp("(^|\\.)" + p.join("\\.(?:.*\\.|)") + "(\\.|$)") : null,
            t.result = void 0,
            t.target || (t.target = i),
            e = null == e ? [t] : k.makeArray(e, [t]),
            c = k.event.special[d] || {},
            n || !c.trigger || !1 !== c.trigger.apply(i, e))) {
                if (!n && !c.noBubble && !y(i)) {
                    for (a = c.delegateType || d,
                    me.test(a + d) || (o = o.parentNode); o; o = o.parentNode)
                        f.push(o),
                        s = o;
                    s === (i.ownerDocument || C) && f.push(s.defaultView || s.parentWindow || T)
                }
                for (r = 0; (o = f[r++]) && !t.isPropagationStopped(); )
                    h = o,
                    t.type = 1 < r ? a : c.bindType || d,
                    (u = (Y.get(o, "events") || {})[t.type] && Y.get(o, "handle")) && u.apply(o, e),
                    (u = l && o[l]) && u.apply && X(o) && (t.result = u.apply(o, e),
                    !1 === t.result && t.preventDefault());
                return t.type = d,
                n || t.isDefaultPrevented() || c._default && !1 !== c._default.apply(f.pop(), e) || !X(i) || l && v(i[d]) && !y(i) && ((s = i[l]) && (i[l] = null),
                k.event.triggered = d,
                t.isPropagationStopped() && h.addEventListener(d, ve),
                i[d](),
                t.isPropagationStopped() && h.removeEventListener(d, ve),
                k.event.triggered = void 0,
                s && (i[l] = s)),
                t.result
            }
        },
        simulate: function(t, e, i) {
            var n = k.extend(new k.Event, i, {
                type: t,
                isSimulated: !0
            });
            k.event.trigger(n, null, e)
        }
    }),
    k.fn.extend({
        trigger: function(t, e) {
            return this.each(function() {
                k.event.trigger(t, e, this)
            })
        },
        triggerHandler: function(t, e) {
            var i = this[0];
            if (i)
                return k.event.trigger(t, e, i, !0)
        }
    }),
    m.focusin || k.each({
        focus: "focusin",
        blur: "focusout"
    }, function(i, n) {
        var r = function(t) {
            k.event.simulate(n, t.target, k.event.fix(t))
        };
        k.event.special[n] = {
            setup: function() {
                var t = this.ownerDocument || this
                  , e = Y.access(t, n);
                e || t.addEventListener(i, r, !0),
                Y.access(t, n, (e || 0) + 1)
            },
            teardown: function() {
                var t = this.ownerDocument || this
                  , e = Y.access(t, n) - 1;
                e ? Y.access(t, n, e) : (t.removeEventListener(i, r, !0),
                Y.remove(t, n))
            }
        }
    });
    var ye = T.location
      , we = Date.now()
      , be = /\?/;
    k.parseXML = function(t) {
        var e;
        if (!t || "string" != typeof t)
            return null;
        try {
            e = (new T.DOMParser).parseFromString(t, "text/xml")
        } catch (t) {
            e = void 0
        }
        return e && !e.getElementsByTagName("parsererror").length || k.error("Invalid XML: " + t),
        e
    }
    ;
    var xe = /\[\]$/
      , Te = /\r?\n/g
      , Ce = /^(?:submit|button|image|reset|file)$/i
      , ke = /^(?:input|select|textarea|keygen)/i;
    function Se(i, t, n, r) {
        var e;
        if (Array.isArray(t))
            k.each(t, function(t, e) {
                n || xe.test(i) ? r(i, e) : Se(i + "[" + ("object" == typeof e && null != e ? t : "") + "]", e, n, r)
            });
        else if (n || "object" !== b(t))
            r(i, t);
        else
            for (e in t)
                Se(i + "[" + e + "]", t[e], n, r)
    }
    k.param = function(t, e) {
        var i, n = [], r = function(t, e) {
            var i = v(e) ? e() : e;
            n[n.length] = encodeURIComponent(t) + "=" + encodeURIComponent(null == i ? "" : i)
        };
        if (Array.isArray(t) || t.jquery && !k.isPlainObject(t))
            k.each(t, function() {
                r(this.name, this.value)
            });
        else
            for (i in t)
                Se(i, t[i], e, r);
        return n.join("&")
    }
    ,
    k.fn.extend({
        serialize: function() {
            return k.param(this.serializeArray())
        },
        serializeArray: function() {
            return this.map(function() {
                var t = k.prop(this, "elements");
                return t ? k.makeArray(t) : this
            }).filter(function() {
                var t = this.type;
                return this.name && !k(this).is(":disabled") && ke.test(this.nodeName) && !Ce.test(t) && (this.checked || !lt.test(t))
            }).map(function(t, e) {
                var i = k(this).val();
                return null == i ? null : Array.isArray(i) ? k.map(i, function(t) {
                    return {
                        name: e.name,
                        value: t.replace(Te, "\r\n")
                    }
                }) : {
                    name: e.name,
                    value: i.replace(Te, "\r\n")
                }
            }).get()
        }
    });
    var $e = /%20/g
      , De = /#.*$/
      , Ae = /([?&])_=[^&]*/
      , Ee = /^(.*?):[ \t]*([^\r\n]*)$/gm
      , Pe = /^(?:GET|HEAD)$/
      , Oe = /^\/\//
      , Re = {}
      , Me = {}
      , je = "*/".concat("*")
      , ze = C.createElement("a");
    function Fe(o) {
        return function(t, e) {
            "string" != typeof t && (e = t,
            t = "*");
            var i, n = 0, r = t.toLowerCase().match(j) || [];
            if (v(e))
                for (; i = r[n++]; )
                    "+" === i[0] ? (i = i.slice(1) || "*",
                    (o[i] = o[i] || []).unshift(e)) : (o[i] = o[i] || []).push(e)
        }
    }
    function Ie(e, r, o, s) {
        var a = {}
          , l = e === Me;
        function u(t) {
            var n;
            return a[t] = !0,
            k.each(e[t] || [], function(t, e) {
                var i = e(r, o, s);
                return "string" != typeof i || l || a[i] ? l ? !(n = i) : void 0 : (r.dataTypes.unshift(i),
                u(i),
                !1)
            }),
            n
        }
        return u(r.dataTypes[0]) || !a["*"] && u("*")
    }
    function Ne(t, e) {
        var i, n, r = k.ajaxSettings.flatOptions || {};
        for (i in e)
            void 0 !== e[i] && ((r[i] ? t : n || (n = {}))[i] = e[i]);
        return n && k.extend(!0, t, n),
        t
    }
    ze.href = ye.href,
    k.extend({
        active: 0,
        lastModified: {},
        etag: {},
        ajaxSettings: {
            url: ye.href,
            type: "GET",
            isLocal: /^(?:about|app|app-storage|.+-extension|file|res|widget):$/.test(ye.protocol),
            global: !0,
            processData: !0,
            async: !0,
            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            accepts: {
                "*": je,
                text: "text/plain",
                html: "text/html",
                xml: "application/xml, text/xml",
                json: "application/json, text/javascript"
            },
            contents: {
                xml: /\bxml\b/,
                html: /\bhtml/,
                json: /\bjson\b/
            },
            responseFields: {
                xml: "responseXML",
                text: "responseText",
                json: "responseJSON"
            },
            converters: {
                "* text": String,
                "text html": !0,
                "text json": JSON.parse,
                "text xml": k.parseXML
            },
            flatOptions: {
                url: !0,
                context: !0
            }
        },
        ajaxSetup: function(t, e) {
            return e ? Ne(Ne(t, k.ajaxSettings), e) : Ne(k.ajaxSettings, t)
        },
        ajaxPrefilter: Fe(Re),
        ajaxTransport: Fe(Me),
        ajax: function(t, e) {
            "object" == typeof t && (e = t,
            t = void 0),
            e = e || {};
            var c, h, f, i, d, n, p, g, r, o, _ = k.ajaxSetup({}, e), m = _.context || _, v = _.context && (m.nodeType || m.jquery) ? k(m) : k.event, y = k.Deferred(), w = k.Callbacks("once memory"), b = _.statusCode || {}, s = {}, a = {}, l = "canceled", x = {
                readyState: 0,
                getResponseHeader: function(t) {
                    var e;
                    if (p) {
                        if (!i)
                            for (i = {}; e = Ee.exec(f); )
                                i[e[1].toLowerCase()] = e[2];
                        e = i[t.toLowerCase()]
                    }
                    return null == e ? null : e
                },
                getAllResponseHeaders: function() {
                    return p ? f : null
                },
                setRequestHeader: function(t, e) {
                    return null == p && (t = a[t.toLowerCase()] = a[t.toLowerCase()] || t,
                    s[t] = e),
                    this
                },
                overrideMimeType: function(t) {
                    return null == p && (_.mimeType = t),
                    this
                },
                statusCode: function(t) {
                    var e;
                    if (t)
                        if (p)
                            x.always(t[x.status]);
                        else
                            for (e in t)
                                b[e] = [b[e], t[e]];
                    return this
                },
                abort: function(t) {
                    var e = t || l;
                    return c && c.abort(e),
                    u(0, e),
                    this
                }
            };
            if (y.promise(x),
            _.url = ((t || _.url || ye.href) + "").replace(Oe, ye.protocol + "//"),
            _.type = e.method || e.type || _.method || _.type,
            _.dataTypes = (_.dataType || "*").toLowerCase().match(j) || [""],
            null == _.crossDomain) {
                n = C.createElement("a");
                try {
                    n.href = _.url,
                    n.href = n.href,
                    _.crossDomain = ze.protocol + "//" + ze.host != n.protocol + "//" + n.host
                } catch (t) {
                    _.crossDomain = !0
                }
            }
            if (_.data && _.processData && "string" != typeof _.data && (_.data = k.param(_.data, _.traditional)),
            Ie(Re, _, e, x),
            p)
                return x;
            for (r in (g = k.event && _.global) && 0 == k.active++ && k.event.trigger("ajaxStart"),
            _.type = _.type.toUpperCase(),
            _.hasContent = !Pe.test(_.type),
            h = _.url.replace(De, ""),
            _.hasContent ? _.data && _.processData && 0 === (_.contentType || "").indexOf("application/x-www-form-urlencoded") && (_.data = _.data.replace($e, "+")) : (o = _.url.slice(h.length),
            _.data && (_.processData || "string" == typeof _.data) && (h += (be.test(h) ? "&" : "?") + _.data,
            delete _.data),
            !1 === _.cache && (h = h.replace(Ae, "$1"),
            o = (be.test(h) ? "&" : "?") + "_=" + we++ + o),
            _.url = h + o),
            _.ifModified && (k.lastModified[h] && x.setRequestHeader("If-Modified-Since", k.lastModified[h]),
            k.etag[h] && x.setRequestHeader("If-None-Match", k.etag[h])),
            (_.data && _.hasContent && !1 !== _.contentType || e.contentType) && x.setRequestHeader("Content-Type", _.contentType),
            x.setRequestHeader("Accept", _.dataTypes[0] && _.accepts[_.dataTypes[0]] ? _.accepts[_.dataTypes[0]] + ("*" !== _.dataTypes[0] ? ", " + je + "; q=0.01" : "") : _.accepts["*"]),
            _.headers)
                x.setRequestHeader(r, _.headers[r]);
            if (_.beforeSend && (!1 === _.beforeSend.call(m, x, _) || p))
                return x.abort();
            if (l = "abort",
            w.add(_.complete),
            x.done(_.success),
            x.fail(_.error),
            c = Ie(Me, _, e, x)) {
                if (x.readyState = 1,
                g && v.trigger("ajaxSend", [x, _]),
                p)
                    return x;
                _.async && 0 < _.timeout && (d = T.setTimeout(function() {
                    x.abort("timeout")
                }, _.timeout));
                try {
                    p = !1,
                    c.send(s, u)
                } catch (t) {
                    if (p)
                        throw t;
                    u(-1, t)
                }
            } else
                u(-1, "No Transport");
            function u(t, e, i, n) {
                var r, o, s, a, l, u = e;
                p || (p = !0,
                d && T.clearTimeout(d),
                c = void 0,
                f = n || "",
                x.readyState = 0 < t ? 4 : 0,
                r = 200 <= t && t < 300 || 304 === t,
                i && (a = function(t, e, i) {
                    for (var n, r, o, s, a = t.contents, l = t.dataTypes; "*" === l[0]; )
                        l.shift(),
                        void 0 === n && (n = t.mimeType || e.getResponseHeader("Content-Type"));
                    if (n)
                        for (r in a)
                            if (a[r] && a[r].test(n)) {
                                l.unshift(r);
                                break
                            }
                    if (l[0]in i)
                        o = l[0];
                    else {
                        for (r in i) {
                            if (!l[0] || t.converters[r + " " + l[0]]) {
                                o = r;
                                break
                            }
                            s || (s = r)
                        }
                        o = o || s
                    }
                    if (o)
                        return o !== l[0] && l.unshift(o),
                        i[o]
                }(_, x, i)),
                a = function(t, e, i, n) {
                    var r, o, s, a, l, u = {}, c = t.dataTypes.slice();
                    if (c[1])
                        for (s in t.converters)
                            u[s.toLowerCase()] = t.converters[s];
                    for (o = c.shift(); o; )
                        if (t.responseFields[o] && (i[t.responseFields[o]] = e),
                        !l && n && t.dataFilter && (e = t.dataFilter(e, t.dataType)),
                        l = o,
                        o = c.shift())
                            if ("*" === o)
                                o = l;
                            else if ("*" !== l && l !== o) {
                                if (!(s = u[l + " " + o] || u["* " + o]))
                                    for (r in u)
                                        if ((a = r.split(" "))[1] === o && (s = u[l + " " + a[0]] || u["* " + a[0]])) {
                                            !0 === s ? s = u[r] : !0 !== u[r] && (o = a[0],
                                            c.unshift(a[1]));
                                            break
                                        }
                                if (!0 !== s)
                                    if (s && t.throws)
                                        e = s(e);
                                    else
                                        try {
                                            e = s(e)
                                        } catch (t) {
                                            return {
                                                state: "parsererror",
                                                error: s ? t : "No conversion from " + l + " to " + o
                                            }
                                        }
                            }
                    return {
                        state: "success",
                        data: e
                    }
                }(_, a, x, r),
                r ? (_.ifModified && ((l = x.getResponseHeader("Last-Modified")) && (k.lastModified[h] = l),
                (l = x.getResponseHeader("etag")) && (k.etag[h] = l)),
                204 === t || "HEAD" === _.type ? u = "nocontent" : 304 === t ? u = "notmodified" : (u = a.state,
                o = a.data,
                r = !(s = a.error))) : (s = u,
                !t && u || (u = "error",
                t < 0 && (t = 0))),
                x.status = t,
                x.statusText = (e || u) + "",
                r ? y.resolveWith(m, [o, u, x]) : y.rejectWith(m, [x, u, s]),
                x.statusCode(b),
                b = void 0,
                g && v.trigger(r ? "ajaxSuccess" : "ajaxError", [x, _, r ? o : s]),
                w.fireWith(m, [x, u]),
                g && (v.trigger("ajaxComplete", [x, _]),
                --k.active || k.event.trigger("ajaxStop")))
            }
            return x
        },
        getJSON: function(t, e, i) {
            return k.get(t, e, i, "json")
        },
        getScript: function(t, e) {
            return k.get(t, void 0, e, "script")
        }
    }),
    k.each(["get", "post"], function(t, r) {
        k[r] = function(t, e, i, n) {
            return v(e) && (n = n || i,
            i = e,
            e = void 0),
            k.ajax(k.extend({
                url: t,
                type: r,
                dataType: n,
                data: e,
                success: i
            }, k.isPlainObject(t) && t))
        }
    }),
    k._evalUrl = function(t) {
        return k.ajax({
            url: t,
            type: "GET",
            dataType: "script",
            cache: !0,
            async: !1,
            global: !1,
            throws: !0
        })
    }
    ,
    k.fn.extend({
        wrapAll: function(t) {
            var e;
            return this[0] && (v(t) && (t = t.call(this[0])),
            e = k(t, this[0].ownerDocument).eq(0).clone(!0),
            this[0].parentNode && e.insertBefore(this[0]),
            e.map(function() {
                for (var t = this; t.firstElementChild; )
                    t = t.firstElementChild;
                return t
            }).append(this)),
            this
        },
        wrapInner: function(i) {
            return v(i) ? this.each(function(t) {
                k(this).wrapInner(i.call(this, t))
            }) : this.each(function() {
                var t = k(this)
                  , e = t.contents();
                e.length ? e.wrapAll(i) : t.append(i)
            })
        },
        wrap: function(e) {
            var i = v(e);
            return this.each(function(t) {
                k(this).wrapAll(i ? e.call(this, t) : e)
            })
        },
        unwrap: function(t) {
            return this.parent(t).not("body").each(function() {
                k(this).replaceWith(this.childNodes)
            }),
            this
        }
    }),
    k.expr.pseudos.hidden = function(t) {
        return !k.expr.pseudos.visible(t)
    }
    ,
    k.expr.pseudos.visible = function(t) {
        return !!(t.offsetWidth || t.offsetHeight || t.getClientRects().length)
    }
    ,
    k.ajaxSettings.xhr = function() {
        try {
            return new T.XMLHttpRequest
        } catch (t) {}
    }
    ;
    var Be = {
        0: 200,
        1223: 204
    }
      , Le = k.ajaxSettings.xhr();
    m.cors = !!Le && "withCredentials"in Le,
    m.ajax = Le = !!Le,
    k.ajaxTransport(function(r) {
        var o, s;
        if (m.cors || Le && !r.crossDomain)
            return {
                send: function(t, e) {
                    var i, n = r.xhr();
                    if (n.open(r.type, r.url, r.async, r.username, r.password),
                    r.xhrFields)
                        for (i in r.xhrFields)
                            n[i] = r.xhrFields[i];
                    for (i in r.mimeType && n.overrideMimeType && n.overrideMimeType(r.mimeType),
                    r.crossDomain || t["X-Requested-With"] || (t["X-Requested-With"] = "XMLHttpRequest"),
                    t)
                        n.setRequestHeader(i, t[i]);
                    o = function(t) {
                        return function() {
                            o && (o = s = n.onload = n.onerror = n.onabort = n.ontimeout = n.onreadystatechange = null,
                            "abort" === t ? n.abort() : "error" === t ? "number" != typeof n.status ? e(0, "error") : e(n.status, n.statusText) : e(Be[n.status] || n.status, n.statusText, "text" !== (n.responseType || "text") || "string" != typeof n.responseText ? {
                                binary: n.response
                            } : {
                                text: n.responseText
                            }, n.getAllResponseHeaders()))
                        }
                    }
                    ,
                    n.onload = o(),
                    s = n.onerror = n.ontimeout = o("error"),
                    void 0 !== n.onabort ? n.onabort = s : n.onreadystatechange = function() {
                        4 === n.readyState && T.setTimeout(function() {
                            o && s()
                        })
                    }
                    ,
                    o = o("abort");
                    try {
                        n.send(r.hasContent && r.data || null)
                    } catch (t) {
                        if (o)
                            throw t
                    }
                },
                abort: function() {
                    o && o()
                }
            }
    }),
    k.ajaxPrefilter(function(t) {
        t.crossDomain && (t.contents.script = !1)
    }),
    k.ajaxSetup({
        accepts: {
            script: "text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"
        },
        contents: {
            script: /\b(?:java|ecma)script\b/
        },
        converters: {
            "text script": function(t) {
                return k.globalEval(t),
                t
            }
        }
    }),
    k.ajaxPrefilter("script", function(t) {
        void 0 === t.cache && (t.cache = !1),
        t.crossDomain && (t.type = "GET")
    }),
    k.ajaxTransport("script", function(i) {
        var n, r;
        if (i.crossDomain)
            return {
                send: function(t, e) {
                    n = k("<script>").prop({
                        charset: i.scriptCharset,
                        src: i.url
                    }).on("load error", r = function(t) {
                        n.remove(),
                        r = null,
                        t && e("error" === t.type ? 404 : 200, t.type)
                    }
                    ),
                    C.head.appendChild(n[0])
                },
                abort: function() {
                    r && r()
                }
            }
    });
    var qe, We = [], He = /(=)\?(?=&|$)|\?\?/;
    k.ajaxSetup({
        jsonp: "callback",
        jsonpCallback: function() {
            var t = We.pop() || k.expando + "_" + we++;
            return this[t] = !0,
            t
        }
    }),
    k.ajaxPrefilter("json jsonp", function(t, e, i) {
        var n, r, o, s = !1 !== t.jsonp && (He.test(t.url) ? "url" : "string" == typeof t.data && 0 === (t.contentType || "").indexOf("application/x-www-form-urlencoded") && He.test(t.data) && "data");
        if (s || "jsonp" === t.dataTypes[0])
            return n = t.jsonpCallback = v(t.jsonpCallback) ? t.jsonpCallback() : t.jsonpCallback,
            s ? t[s] = t[s].replace(He, "$1" + n) : !1 !== t.jsonp && (t.url += (be.test(t.url) ? "&" : "?") + t.jsonp + "=" + n),
            t.converters["script json"] = function() {
                return o || k.error(n + " was not called"),
                o[0]
            }
            ,
            t.dataTypes[0] = "json",
            r = T[n],
            T[n] = function() {
                o = arguments
            }
            ,
            i.always(function() {
                void 0 === r ? k(T).removeProp(n) : T[n] = r,
                t[n] && (t.jsonpCallback = e.jsonpCallback,
                We.push(n)),
                o && v(r) && r(o[0]),
                o = r = void 0
            }),
            "script"
    }),
    m.createHTMLDocument = ((qe = C.implementation.createHTMLDocument("").body).innerHTML = "<form></form><form></form>",
    2 === qe.childNodes.length),
    k.parseHTML = function(t, e, i) {
        return "string" != typeof t ? [] : ("boolean" == typeof e && (i = e,
        e = !1),
        e || (m.createHTMLDocument ? ((n = (e = C.implementation.createHTMLDocument("")).createElement("base")).href = C.location.href,
        e.head.appendChild(n)) : e = C),
        o = !i && [],
        (r = D.exec(t)) ? [e.createElement(r[1])] : (r = mt([t], e, o),
        o && o.length && k(o).remove(),
        k.merge([], r.childNodes)));
        var n, r, o
    }
    ,
    k.fn.load = function(t, e, i) {
        var n, r, o, s = this, a = t.indexOf(" ");
        return -1 < a && (n = de(t.slice(a)),
        t = t.slice(0, a)),
        v(e) ? (i = e,
        e = void 0) : e && "object" == typeof e && (r = "POST"),
        0 < s.length && k.ajax({
            url: t,
            type: r || "GET",
            dataType: "html",
            data: e
        }).done(function(t) {
            o = arguments,
            s.html(n ? k("<div>").append(k.parseHTML(t)).find(n) : t)
        }).always(i && function(t, e) {
            s.each(function() {
                i.apply(this, o || [t.responseText, e, t])
            })
        }
        ),
        this
    }
    ,
    k.each(["ajaxStart", "ajaxStop", "ajaxComplete", "ajaxError", "ajaxSuccess", "ajaxSend"], function(t, e) {
        k.fn[e] = function(t) {
            return this.on(e, t)
        }
    }),
    k.expr.pseudos.animated = function(e) {
        return k.grep(k.timers, function(t) {
            return e === t.elem
        }).length
    }
    ,
    k.offset = {
        setOffset: function(t, e, i) {
            var n, r, o, s, a, l, u = k.css(t, "position"), c = k(t), h = {};
            "static" === u && (t.style.position = "relative"),
            a = c.offset(),
            o = k.css(t, "top"),
            l = k.css(t, "left"),
            r = ("absolute" === u || "fixed" === u) && -1 < (o + l).indexOf("auto") ? (s = (n = c.position()).top,
            n.left) : (s = parseFloat(o) || 0,
            parseFloat(l) || 0),
            v(e) && (e = e.call(t, i, k.extend({}, a))),
            null != e.top && (h.top = e.top - a.top + s),
            null != e.left && (h.left = e.left - a.left + r),
            "using"in e ? e.using.call(t, h) : c.css(h)
        }
    },
    k.fn.extend({
        offset: function(e) {
            if (arguments.length)
                return void 0 === e ? this : this.each(function(t) {
                    k.offset.setOffset(this, e, t)
                });
            var t, i, n = this[0];
            return n ? n.getClientRects().length ? (t = n.getBoundingClientRect(),
            i = n.ownerDocument.defaultView,
            {
                top: t.top + i.pageYOffset,
                left: t.left + i.pageXOffset
            }) : {
                top: 0,
                left: 0
            } : void 0
        },
        position: function() {
            if (this[0]) {
                var t, e, i, n = this[0], r = {
                    top: 0,
                    left: 0
                };
                if ("fixed" === k.css(n, "position"))
                    e = n.getBoundingClientRect();
                else {
                    for (e = this.offset(),
                    i = n.ownerDocument,
                    t = n.offsetParent || i.documentElement; t && (t === i.body || t === i.documentElement) && "static" === k.css(t, "position"); )
                        t = t.parentNode;
                    t && t !== n && 1 === t.nodeType && ((r = k(t).offset()).top += k.css(t, "borderTopWidth", !0),
                    r.left += k.css(t, "borderLeftWidth", !0))
                }
                return {
                    top: e.top - r.top - k.css(n, "marginTop", !0),
                    left: e.left - r.left - k.css(n, "marginLeft", !0)
                }
            }
        },
        offsetParent: function() {
            return this.map(function() {
                for (var t = this.offsetParent; t && "static" === k.css(t, "position"); )
                    t = t.offsetParent;
                return t || vt
            })
        }
    }),
    k.each({
        scrollLeft: "pageXOffset",
        scrollTop: "pageYOffset"
    }, function(e, r) {
        var o = "pageYOffset" === r;
        k.fn[e] = function(t) {
            return q(this, function(t, e, i) {
                var n;
                if (y(t) ? n = t : 9 === t.nodeType && (n = t.defaultView),
                void 0 === i)
                    return n ? n[r] : t[e];
                n ? n.scrollTo(o ? n.pageXOffset : i, o ? i : n.pageYOffset) : t[e] = i
            }, e, t, arguments.length)
        }
    }),
    k.each(["top", "left"], function(t, i) {
        k.cssHooks[i] = Bt(m.pixelPosition, function(t, e) {
            if (e)
                return e = Nt(t, i),
                zt.test(e) ? k(t).position()[i] + "px" : e
        })
    }),
    k.each({
        Height: "height",
        Width: "width"
    }, function(s, a) {
        k.each({
            padding: "inner" + s,
            content: a,
            "": "outer" + s
        }, function(n, o) {
            k.fn[o] = function(t, e) {
                var i = arguments.length && (n || "boolean" != typeof t)
                  , r = n || (!0 === t || !0 === e ? "margin" : "border");
                return q(this, function(t, e, i) {
                    var n;
                    return y(t) ? 0 === o.indexOf("outer") ? t["inner" + s] : t.document.documentElement["client" + s] : 9 === t.nodeType ? (n = t.documentElement,
                    Math.max(t.body["scroll" + s], n["scroll" + s], t.body["offset" + s], n["offset" + s], n["client" + s])) : void 0 === i ? k.css(t, e, r) : k.style(t, e, i, r)
                }, a, i ? t : void 0, i)
            }
        })
    }),
    k.each("blur focus focusin focusout resize scroll click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup contextmenu".split(" "), function(t, i) {
        k.fn[i] = function(t, e) {
            return 0 < arguments.length ? this.on(i, null, t, e) : this.trigger(i)
        }
    }),
    k.fn.extend({
        hover: function(t, e) {
            return this.mouseenter(t).mouseleave(e || t)
        }
    }),
    k.fn.extend({
        bind: function(t, e, i) {
            return this.on(t, null, e, i)
        },
        unbind: function(t, e) {
            return this.off(t, null, e)
        },
        delegate: function(t, e, i, n) {
            return this.on(e, t, i, n)
        },
        undelegate: function(t, e, i) {
            return 1 === arguments.length ? this.off(t, "**") : this.off(e, t || "**", i)
        }
    }),
    k.proxy = function(t, e) {
        var i, n, r;
        if ("string" == typeof e && (i = t[e],
        e = t,
        t = i),
        v(t))
            return n = a.call(arguments, 2),
            (r = function() {
                return t.apply(e || this, n.concat(a.call(arguments)))
            }
            ).guid = t.guid = t.guid || k.guid++,
            r
    }
    ,
    k.holdReady = function(t) {
        t ? k.readyWait++ : k.ready(!0)
    }
    ,
    k.isArray = Array.isArray,
    k.parseJSON = JSON.parse,
    k.nodeName = $,
    k.isFunction = v,
    k.isWindow = y,
    k.camelCase = V,
    k.type = b,
    k.now = Date.now,
    k.isNumeric = function(t) {
        var e = k.type(t);
        return ("number" === e || "string" === e) && !isNaN(t - parseFloat(t))
    }
    ,
    "function" == typeof define && define.amd && define("jquery", [], function() {
        return k
    });
    var Ue = T.jQuery
      , Ve = T.$;
    return k.noConflict = function(t) {
        return T.$ === k && (T.$ = Ve),
        t && T.jQuery === k && (T.jQuery = Ue),
        k
    }
    ,
    t || (T.jQuery = T.$ = k),
    k
}),
function(t) {
    if ("object" == typeof exports && "undefined" != typeof module)
        module.exports = t();
    else if ("function" == typeof define && define.amd)
        define([], t);
    else {
        ("undefined" != typeof window ? window : "undefined" != typeof global ? global : "undefined" != typeof self ? self : this).enquire = t()
    }
}(function() {
    return function o(s, a, l) {
        function u(i, t) {
            if (!a[i]) {
                if (!s[i]) {
                    var e = "function" == typeof require && require;
                    if (!t && e)
                        return e(i, !0);
                    if (c)
                        return c(i, !0);
                    var n = new Error("Cannot find module '" + i + "'");
                    throw n.code = "MODULE_NOT_FOUND",
                    n
                }
                var r = a[i] = {
                    exports: {}
                };
                s[i][0].call(r.exports, function(t) {
                    var e = s[i][1][t];
                    return u(e || t)
                }, r, r.exports, o, s, a, l)
            }
            return a[i].exports
        }
        for (var c = "function" == typeof require && require, t = 0; t < l.length; t++)
            u(l[t]);
        return u
    }({
        1: [function(t, e, i) {
            function n(t, e) {
                this.query = t,
                this.isUnconditional = e,
                this.handlers = [],
                this.mql = window.matchMedia(t);
                var i = this;
                this.listener = function(t) {
                    i.mql = t.currentTarget || t,
                    i.assess()
                }
                ,
                this.mql.addListener(this.listener)
            }
            var r = t(3)
              , o = t(4).each;
            n.prototype = {
                constuctor: n,
                addHandler: function(t) {
                    var e = new r(t);
                    this.handlers.push(e),
                    this.matches() && e.on()
                },
                removeHandler: function(i) {
                    var n = this.handlers;
                    o(n, function(t, e) {
                        if (t.equals(i))
                            return t.destroy(),
                            !n.splice(e, 1)
                    })
                },
                matches: function() {
                    return this.mql.matches || this.isUnconditional
                },
                clear: function() {
                    o(this.handlers, function(t) {
                        t.destroy()
                    }),
                    this.mql.removeListener(this.listener),
                    this.handlers.length = 0
                },
                assess: function() {
                    var e = this.matches() ? "on" : "off";
                    o(this.handlers, function(t) {
                        t[e]()
                    })
                }
            },
            e.exports = n
        }
        , {
            3: 3,
            4: 4
        }],
        2: [function(t, e, i) {
            function n() {
                if (!window.matchMedia)
                    throw new Error("matchMedia not present, legacy browsers require a polyfill");
                this.queries = {},
                this.browserIsIncapable = !window.matchMedia("only all").matches
            }
            var o = t(1)
              , r = t(4)
              , s = r.each
              , a = r.isFunction
              , l = r.isArray;
            n.prototype = {
                constructor: n,
                register: function(e, t, i) {
                    var n = this.queries
                      , r = i && this.browserIsIncapable;
                    return n[e] || (n[e] = new o(e,r)),
                    a(t) && (t = {
                        match: t
                    }),
                    l(t) || (t = [t]),
                    s(t, function(t) {
                        a(t) && (t = {
                            match: t
                        }),
                        n[e].addHandler(t)
                    }),
                    this
                },
                unregister: function(t, e) {
                    var i = this.queries[t];
                    return i && (e ? i.removeHandler(e) : (i.clear(),
                    delete this.queries[t])),
                    this
                }
            },
            e.exports = n
        }
        , {
            1: 1,
            4: 4
        }],
        3: [function(t, e, i) {
            function n(t) {
                !(this.options = t).deferSetup && this.setup()
            }
            n.prototype = {
                constructor: n,
                setup: function() {
                    this.options.setup && this.options.setup(),
                    this.initialised = !0
                },
                on: function() {
                    !this.initialised && this.setup(),
                    this.options.match && this.options.match()
                },
                off: function() {
                    this.options.unmatch && this.options.unmatch()
                },
                destroy: function() {
                    this.options.destroy ? this.options.destroy() : this.off()
                },
                equals: function(t) {
                    return this.options === t || this.options.match === t
                }
            },
            e.exports = n
        }
        , {}],
        4: [function(t, e, i) {
            e.exports = {
                isFunction: function(t) {
                    return "function" == typeof t
                },
                isArray: function(t) {
                    return "[object Array]" === Object.prototype.toString.apply(t)
                },
                each: function(t, e) {
                    for (var i = 0, n = t.length; i < n && !1 !== e(t[i], i); i++)
                        ;
                }
            }
        }
        , {}],
        5: [function(t, e, i) {
            var n = t(2);
            e.exports = new n
        }
        , {
            2: 2
        }]
    }, {}, [5])(5)
}),
function() {
    function Bo(t, e) {
        return t.set(e[0], e[1]),
        t
    }
    function Lo(t, e) {
        return t.add(e),
        t
    }
    function qo(t, e, i) {
        switch (i.length) {
        case 0:
            return t.call(e);
        case 1:
            return t.call(e, i[0]);
        case 2:
            return t.call(e, i[0], i[1]);
        case 3:
            return t.call(e, i[0], i[1], i[2])
        }
        return t.apply(e, i)
    }
    function Wo(t, e, i, n) {
        for (var r = -1, o = t ? t.length : 0; ++r < o; ) {
            var s = t[r];
            e(n, s, i(s), t)
        }
        return n
    }
    function Ho(t, e) {
        for (var i = -1, n = t ? t.length : 0; ++i < n && !1 !== e(t[i], i, t); )
            ;
        return t
    }
    function Uo(t, e) {
        for (var i = -1, n = t ? t.length : 0; ++i < n; )
            if (!e(t[i], i, t))
                return !1;
        return !0
    }
    function Vo(t, e) {
        for (var i = -1, n = t ? t.length : 0, r = 0, o = []; ++i < n; ) {
            var s = t[i];
            e(s, i, t) && (o[r++] = s)
        }
        return o
    }
    function Xo(t, e) {
        return !(!t || !t.length) && -1 < is(t, e, 0)
    }
    function Go(t, e, i) {
        for (var n = -1, r = t ? t.length : 0; ++n < r; )
            if (i(e, t[n]))
                return !0;
        return !1
    }
    function Yo(t, e) {
        for (var i = -1, n = t ? t.length : 0, r = Array(n); ++i < n; )
            r[i] = e(t[i], i, t);
        return r
    }
    function Zo(t, e) {
        for (var i = -1, n = e.length, r = t.length; ++i < n; )
            t[r + i] = e[i];
        return t
    }
    function Qo(t, e, i, n) {
        var r = -1
          , o = t ? t.length : 0;
        for (n && o && (i = t[++r]); ++r < o; )
            i = e(i, t[r], r, t);
        return i
    }
    function Ko(t, e, i, n) {
        var r = t ? t.length : 0;
        for (n && r && (i = t[--r]); r--; )
            i = e(i, t[r], r, t);
        return i
    }
    function Jo(t, e) {
        for (var i = -1, n = t ? t.length : 0; ++i < n; )
            if (e(t[i], i, t))
                return !0;
        return !1
    }
    function ts(t, n, e) {
        var r;
        return e(t, function(t, e, i) {
            if (n(t, e, i))
                return r = e,
                !1
        }),
        r
    }
    function es(t, e, i, n) {
        var r = t.length;
        for (i += n ? 1 : -1; n ? i-- : ++i < r; )
            if (e(t[i], i, t))
                return i;
        return -1
    }
    function is(t, e, i) {
        if (e == e)
            t: {
                --i;
                for (var n = t.length; ++i < n; )
                    if (t[i] === e) {
                        t = i;
                        break t
                    }
                t = -1
            }
        else
            t = es(t, rs, i);
        return t
    }
    function ns(t, e, i, n) {
        --i;
        for (var r = t.length; ++i < r; )
            if (n(t[i], e))
                return i;
        return -1
    }
    function rs(t) {
        return t != t
    }
    function os(t, e) {
        var i = t ? t.length : 0;
        return i ? ls(t, e) / i : Ts
    }
    function ss(e) {
        return function(t) {
            return null == t ? xs : t[e]
        }
    }
    function t(e) {
        return function(t) {
            return null == e ? xs : e[t]
        }
    }
    function as(t, n, r, o, e) {
        return e(t, function(t, e, i) {
            r = o ? (o = !1,
            t) : n(r, t, e, i)
        }),
        r
    }
    function ls(t, e) {
        for (var i, n = -1, r = t.length; ++n < r; ) {
            var o = e(t[n]);
            o !== xs && (i = i === xs ? o : i + o)
        }
        return i
    }
    function us(t, e) {
        for (var i = -1, n = Array(t); ++i < t; )
            n[i] = e(i);
        return n
    }
    function cs(e) {
        return function(t) {
            return e(t)
        }
    }
    function hs(e, t) {
        return Yo(t, function(t) {
            return e[t]
        })
    }
    function fs(t, e) {
        return t.has(e)
    }
    function ds(t, e) {
        for (var i = -1, n = t.length; ++i < n && -1 < is(e, t[i], 0); )
            ;
        return i
    }
    function ps(t, e) {
        for (var i = t.length; i-- && -1 < is(e, t[i], 0); )
            ;
        return i
    }
    function gs(t) {
        return "\\" + o[t]
    }
    function _s(t) {
        var i = -1
          , n = Array(t.size);
        return t.forEach(function(t, e) {
            n[++i] = [e, t]
        }),
        n
    }
    function ms(e, i) {
        return function(t) {
            return e(i(t))
        }
    }
    function vs(t, e) {
        for (var i = -1, n = t.length, r = 0, o = []; ++i < n; ) {
            var s = t[i];
            s !== e && "__lodash_placeholder__" !== s || (t[i] = "__lodash_placeholder__",
            o[r++] = i)
        }
        return o
    }
    function ys(t) {
        var e = -1
          , i = Array(t.size);
        return t.forEach(function(t) {
            i[++e] = t
        }),
        i
    }
    function ws(t) {
        if (la.test(t)) {
            for (var e = n.lastIndex = 0; n.test(t); )
                ++e;
            t = e
        } else
            t = h(t);
        return t
    }
    function bs(t) {
        return la.test(t) ? t.match(n) || [] : t.split("")
    }
    var xs, Ts = NaN, Cs = [["ary", 128], ["bind", 1], ["bindKey", 2], ["curry", 8], ["curryRight", 16], ["flip", 512], ["partial", 32], ["partialRight", 64], ["rearg", 256]], ks = /\b__p\+='';/g, Ss = /\b(__p\+=)''\+/g, $s = /(__e\(.*?\)|\b__t\))\+'';/g, Ds = /&(?:amp|lt|gt|quot|#39);/g, As = /[&<>"']/g, Es = RegExp(Ds.source), Ps = RegExp(As.source), Os = /<%-([\s\S]+?)%>/g, Rs = /<%([\s\S]+?)%>/g, Ms = /<%=([\s\S]+?)%>/g, js = /\.|\[(?:[^[\]]*|(["'])(?:(?!\1)[^\\]|\\.)*?\1)\]/, zs = /^\w*$/, Fs = /^\./, Is = /[^.[\]]+|\[(?:(-?\d+(?:\.\d+)?)|(["'])((?:(?!\2)[^\\]|\\.)*?)\2)\]|(?=(?:\.|\[\])(?:\.|\[\]|$))/g, Ns = /[\\^$.*+?()[\]{}|]/g, Bs = RegExp(Ns.source), Ls = /^\s+|\s+$/g, qs = /^\s+/, Ws = /\s+$/, Hs = /\{(?:\n\/\* \[wrapped with .+\] \*\/)?\n?/, Us = /\{\n\/\* \[wrapped with (.+)\] \*/, Vs = /,? & /, Xs = /[^\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f]+/g, Gs = /\\(\\)?/g, Ys = /\$\{([^\\}]*(?:\\.[^\\}]*)*)\}/g, Zs = /\w*$/, Qs = /^[-+]0x[0-9a-f]+$/i, Ks = /^0b[01]+$/i, Js = /^\[object .+?Constructor\]$/, ta = /^0o[0-7]+$/i, ea = /^(?:0|[1-9]\d*)$/, ia = /[\xc0-\xd6\xd8-\xf6\xf8-\xff\u0100-\u017f]/g, na = /($^)/, ra = /['\n\r\u2028\u2029\\]/g, e = "[\\ufe0e\\ufe0f]?(?:[\\u0300-\\u036f\\ufe20-\\ufe23\\u20d0-\\u20f0]|\\ud83c[\\udffb-\\udfff])?(?:\\u200d(?:[^\\ud800-\\udfff]|(?:\\ud83c[\\udde6-\\uddff]){2}|[\\ud800-\\udbff][\\udc00-\\udfff])[\\ufe0e\\ufe0f]?(?:[\\u0300-\\u036f\\ufe20-\\ufe23\\u20d0-\\u20f0]|\\ud83c[\\udffb-\\udfff])?)*", i = "(?:[\\u2700-\\u27bf]|(?:\\ud83c[\\udde6-\\uddff]){2}|[\\ud800-\\udbff][\\udc00-\\udfff])" + e, oa = RegExp("['’]", "g"), sa = RegExp("[\\u0300-\\u036f\\ufe20-\\ufe23\\u20d0-\\u20f0]", "g"), n = RegExp("\\ud83c[\\udffb-\\udfff](?=\\ud83c[\\udffb-\\udfff])|(?:[^\\ud800-\\udfff][\\u0300-\\u036f\\ufe20-\\ufe23\\u20d0-\\u20f0]?|[\\u0300-\\u036f\\ufe20-\\ufe23\\u20d0-\\u20f0]|(?:\\ud83c[\\udde6-\\uddff]){2}|[\\ud800-\\udbff][\\udc00-\\udfff]|[\\ud800-\\udfff])" + e, "g"), aa = RegExp(["[A-Z\\xc0-\\xd6\\xd8-\\xde]?[a-z\\xdf-\\xf6\\xf8-\\xff]+(?:['’](?:d|ll|m|re|s|t|ve))?(?=[\\xac\\xb1\\xd7\\xf7\\x00-\\x2f\\x3a-\\x40\\x5b-\\x60\\x7b-\\xbf\\u2000-\\u206f \\t\\x0b\\f\\xa0\\ufeff\\n\\r\\u2028\\u2029\\u1680\\u180e\\u2000\\u2001\\u2002\\u2003\\u2004\\u2005\\u2006\\u2007\\u2008\\u2009\\u200a\\u202f\\u205f\\u3000]|[A-Z\\xc0-\\xd6\\xd8-\\xde]|$)|(?:[A-Z\\xc0-\\xd6\\xd8-\\xde]|[^\\ud800-\\udfff\\xac\\xb1\\xd7\\xf7\\x00-\\x2f\\x3a-\\x40\\x5b-\\x60\\x7b-\\xbf\\u2000-\\u206f \\t\\x0b\\f\\xa0\\ufeff\\n\\r\\u2028\\u2029\\u1680\\u180e\\u2000\\u2001\\u2002\\u2003\\u2004\\u2005\\u2006\\u2007\\u2008\\u2009\\u200a\\u202f\\u205f\\u3000\\d+\\u2700-\\u27bfa-z\\xdf-\\xf6\\xf8-\\xffA-Z\\xc0-\\xd6\\xd8-\\xde])+(?:['’](?:D|LL|M|RE|S|T|VE))?(?=[\\xac\\xb1\\xd7\\xf7\\x00-\\x2f\\x3a-\\x40\\x5b-\\x60\\x7b-\\xbf\\u2000-\\u206f \\t\\x0b\\f\\xa0\\ufeff\\n\\r\\u2028\\u2029\\u1680\\u180e\\u2000\\u2001\\u2002\\u2003\\u2004\\u2005\\u2006\\u2007\\u2008\\u2009\\u200a\\u202f\\u205f\\u3000]|[A-Z\\xc0-\\xd6\\xd8-\\xde](?:[a-z\\xdf-\\xf6\\xf8-\\xff]|[^\\ud800-\\udfff\\xac\\xb1\\xd7\\xf7\\x00-\\x2f\\x3a-\\x40\\x5b-\\x60\\x7b-\\xbf\\u2000-\\u206f \\t\\x0b\\f\\xa0\\ufeff\\n\\r\\u2028\\u2029\\u1680\\u180e\\u2000\\u2001\\u2002\\u2003\\u2004\\u2005\\u2006\\u2007\\u2008\\u2009\\u200a\\u202f\\u205f\\u3000\\d+\\u2700-\\u27bfa-z\\xdf-\\xf6\\xf8-\\xffA-Z\\xc0-\\xd6\\xd8-\\xde])|$)|[A-Z\\xc0-\\xd6\\xd8-\\xde]?(?:[a-z\\xdf-\\xf6\\xf8-\\xff]|[^\\ud800-\\udfff\\xac\\xb1\\xd7\\xf7\\x00-\\x2f\\x3a-\\x40\\x5b-\\x60\\x7b-\\xbf\\u2000-\\u206f \\t\\x0b\\f\\xa0\\ufeff\\n\\r\\u2028\\u2029\\u1680\\u180e\\u2000\\u2001\\u2002\\u2003\\u2004\\u2005\\u2006\\u2007\\u2008\\u2009\\u200a\\u202f\\u205f\\u3000\\d+\\u2700-\\u27bfa-z\\xdf-\\xf6\\xf8-\\xffA-Z\\xc0-\\xd6\\xd8-\\xde])+(?:['’](?:d|ll|m|re|s|t|ve))?|[A-Z\\xc0-\\xd6\\xd8-\\xde]+(?:['’](?:D|LL|M|RE|S|T|VE))?|\\d+", i].join("|"), "g"), la = RegExp("[\\u200d\\ud800-\\udfff\\u0300-\\u036f\\ufe20-\\ufe23\\u20d0-\\u20f0\\ufe0e\\ufe0f]"), ua = /[a-z][A-Z]|[A-Z]{2,}[a-z]|[0-9][a-zA-Z]|[a-zA-Z][0-9]|[^a-zA-Z0-9 ]/, ca = "Array Buffer DataView Date Error Float32Array Float64Array Function Int8Array Int16Array Int32Array Map Math Object Promise RegExp Set String Symbol TypeError Uint8Array Uint8ClampedArray Uint16Array Uint32Array WeakMap _ clearTimeout isFinite parseInt setTimeout".split(" "), ha = {};
    ha["[object Float32Array]"] = ha["[object Float64Array]"] = ha["[object Int8Array]"] = ha["[object Int16Array]"] = ha["[object Int32Array]"] = ha["[object Uint8Array]"] = ha["[object Uint8ClampedArray]"] = ha["[object Uint16Array]"] = ha["[object Uint32Array]"] = !0,
    ha["[object Arguments]"] = ha["[object Array]"] = ha["[object ArrayBuffer]"] = ha["[object Boolean]"] = ha["[object DataView]"] = ha["[object Date]"] = ha["[object Error]"] = ha["[object Function]"] = ha["[object Map]"] = ha["[object Number]"] = ha["[object Object]"] = ha["[object RegExp]"] = ha["[object Set]"] = ha["[object String]"] = ha["[object WeakMap]"] = !1;
    var fa = {};
    fa["[object Arguments]"] = fa["[object Array]"] = fa["[object ArrayBuffer]"] = fa["[object DataView]"] = fa["[object Boolean]"] = fa["[object Date]"] = fa["[object Float32Array]"] = fa["[object Float64Array]"] = fa["[object Int8Array]"] = fa["[object Int16Array]"] = fa["[object Int32Array]"] = fa["[object Map]"] = fa["[object Number]"] = fa["[object Object]"] = fa["[object RegExp]"] = fa["[object Set]"] = fa["[object String]"] = fa["[object Symbol]"] = fa["[object Uint8Array]"] = fa["[object Uint8ClampedArray]"] = fa["[object Uint16Array]"] = fa["[object Uint32Array]"] = !0,
    fa["[object Error]"] = fa["[object Function]"] = fa["[object WeakMap]"] = !1;
    var r, o = {
        "\\": "\\",
        "'": "'",
        "\n": "n",
        "\r": "r",
        "\u2028": "u2028",
        "\u2029": "u2029"
    }, da = parseFloat, pa = parseInt, s = "object" == typeof global && global && global.Object === Object && global, a = "object" == typeof self && self && self.Object === Object && self, ga = s || a || Function("return this")(), l = "object" == typeof exports && exports && !exports.nodeType && exports, u = l && "object" == typeof module && module && !module.nodeType && module, _a = u && u.exports === l, c = _a && s.h;
    t: {
        try {
            r = c && c.g("util");
            break t
        } catch (Bo) {}
        r = void 0
    }
    var ma = r && r.isArrayBuffer
      , va = r && r.isDate
      , ya = r && r.isMap
      , wa = r && r.isRegExp
      , ba = r && r.isSet
      , xa = r && r.isTypedArray
      , h = ss("length")
      , Ta = t({
        "À": "A",
        "Á": "A",
        "Â": "A",
        "Ã": "A",
        "Ä": "A",
        "Å": "A",
        "à": "a",
        "á": "a",
        "â": "a",
        "ã": "a",
        "ä": "a",
        "å": "a",
        "Ç": "C",
        "ç": "c",
        "Ð": "D",
        "ð": "d",
        "È": "E",
        "É": "E",
        "Ê": "E",
        "Ë": "E",
        "è": "e",
        "é": "e",
        "ê": "e",
        "ë": "e",
        "Ì": "I",
        "Í": "I",
        "Î": "I",
        "Ï": "I",
        "ì": "i",
        "í": "i",
        "î": "i",
        "ï": "i",
        "Ñ": "N",
        "ñ": "n",
        "Ò": "O",
        "Ó": "O",
        "Ô": "O",
        "Õ": "O",
        "Ö": "O",
        "Ø": "O",
        "ò": "o",
        "ó": "o",
        "ô": "o",
        "õ": "o",
        "ö": "o",
        "ø": "o",
        "Ù": "U",
        "Ú": "U",
        "Û": "U",
        "Ü": "U",
        "ù": "u",
        "ú": "u",
        "û": "u",
        "ü": "u",
        "Ý": "Y",
        "ý": "y",
        "ÿ": "y",
        "Æ": "Ae",
        "æ": "ae",
        "Þ": "Th",
        "þ": "th",
        "ß": "ss",
        "Ā": "A",
        "Ă": "A",
        "Ą": "A",
        "ā": "a",
        "ă": "a",
        "ą": "a",
        "Ć": "C",
        "Ĉ": "C",
        "Ċ": "C",
        "Č": "C",
        "ć": "c",
        "ĉ": "c",
        "ċ": "c",
        "č": "c",
        "Ď": "D",
        "Đ": "D",
        "ď": "d",
        "đ": "d",
        "Ē": "E",
        "Ĕ": "E",
        "Ė": "E",
        "Ę": "E",
        "Ě": "E",
        "ē": "e",
        "ĕ": "e",
        "ė": "e",
        "ę": "e",
        "ě": "e",
        "Ĝ": "G",
        "Ğ": "G",
        "Ġ": "G",
        "Ģ": "G",
        "ĝ": "g",
        "ğ": "g",
        "ġ": "g",
        "ģ": "g",
        "Ĥ": "H",
        "Ħ": "H",
        "ĥ": "h",
        "ħ": "h",
        "Ĩ": "I",
        "Ī": "I",
        "Ĭ": "I",
        "Į": "I",
        "İ": "I",
        "ĩ": "i",
        "ī": "i",
        "ĭ": "i",
        "į": "i",
        "ı": "i",
        "Ĵ": "J",
        "ĵ": "j",
        "Ķ": "K",
        "ķ": "k",
        "ĸ": "k",
        "Ĺ": "L",
        "Ļ": "L",
        "Ľ": "L",
        "Ŀ": "L",
        "Ł": "L",
        "ĺ": "l",
        "ļ": "l",
        "ľ": "l",
        "ŀ": "l",
        "ł": "l",
        "Ń": "N",
        "Ņ": "N",
        "Ň": "N",
        "Ŋ": "N",
        "ń": "n",
        "ņ": "n",
        "ň": "n",
        "ŋ": "n",
        "Ō": "O",
        "Ŏ": "O",
        "Ő": "O",
        "ō": "o",
        "ŏ": "o",
        "ő": "o",
        "Ŕ": "R",
        "Ŗ": "R",
        "Ř": "R",
        "ŕ": "r",
        "ŗ": "r",
        "ř": "r",
        "Ś": "S",
        "Ŝ": "S",
        "Ş": "S",
        "Š": "S",
        "ś": "s",
        "ŝ": "s",
        "ş": "s",
        "š": "s",
        "Ţ": "T",
        "Ť": "T",
        "Ŧ": "T",
        "ţ": "t",
        "ť": "t",
        "ŧ": "t",
        "Ũ": "U",
        "Ū": "U",
        "Ŭ": "U",
        "Ů": "U",
        "Ű": "U",
        "Ų": "U",
        "ũ": "u",
        "ū": "u",
        "ŭ": "u",
        "ů": "u",
        "ű": "u",
        "ų": "u",
        "Ŵ": "W",
        "ŵ": "w",
        "Ŷ": "Y",
        "ŷ": "y",
        "Ÿ": "Y",
        "Ź": "Z",
        "Ż": "Z",
        "Ž": "Z",
        "ź": "z",
        "ż": "z",
        "ž": "z",
        "Ĳ": "IJ",
        "ĳ": "ij",
        "Œ": "Oe",
        "œ": "oe",
        "ŉ": "'n",
        "ſ": "s"
    })
      , Ca = t({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;"
    })
      , ka = t({
        "&amp;": "&",
        "&lt;": "<",
        "&gt;": ">",
        "&quot;": '"',
        "&#39;": "'"
    })
      , Sa = function t(e) {
        function _(t) {
            return Hi.call(t)
        }
        function d(t) {
            if (Qe(t) && !Lr(t) && !(t instanceof g)) {
                if (t instanceof p)
                    return t;
                if (Li.call(t, "__wrapped__"))
                    return Te(t)
            }
            return new p(t)
        }
        function o() {}
        function p(t, e) {
            this.__wrapped__ = t,
            this.__actions__ = [],
            this.__chain__ = !!e,
            this.__index__ = 0,
            this.__values__ = xs
        }
        function g(t) {
            this.__wrapped__ = t,
            this.__actions__ = [],
            this.__dir__ = 1,
            this.__filtered__ = !1,
            this.__iteratees__ = [],
            this.__takeCount__ = 4294967295,
            this.__views__ = []
        }
        function i(t) {
            var e = -1
              , i = t ? t.length : 0;
            for (this.clear(); ++e < i; ) {
                var n = t[e];
                this.set(n[0], n[1])
            }
        }
        function r(t) {
            var e = -1
              , i = t ? t.length : 0;
            for (this.clear(); ++e < i; ) {
                var n = t[e];
                this.set(n[0], n[1])
            }
        }
        function s(t) {
            var e = -1
              , i = t ? t.length : 0;
            for (this.clear(); ++e < i; ) {
                var n = t[e];
                this.set(n[0], n[1])
            }
        }
        function m(t) {
            var e = -1
              , i = t ? t.length : 0;
            for (this.__data__ = new s; ++e < i; )
                this.add(t[e])
        }
        function v(t) {
            this.size = (this.__data__ = new r(t)).size
        }
        function a(t, e) {
            var i, n = Lr(t), r = !n && Br(t), o = !n && !r && Wr(t), s = !n && !r && !o && Gr(t), a = (r = (n = n || r || o || s) ? us(t.length, Mi) : []).length;
            for (i in t)
                !e && !Li.call(t, i) || n && ("length" == i || o && ("offset" == i || "parent" == i) || s && ("buffer" == i || "byteLength" == i || "byteOffset" == i) || le(i, a)) || r.push(i);
            return r
        }
        function n(t) {
            var e = t.length;
            return e ? t[rt(0, e - 1)] : xs
        }
        function h(t, e, i, n) {
            return t === xs || We(t, Fi[i]) && !Li.call(n, i) ? e : t
        }
        function y(t, e, i) {
            (i === xs || We(t[e], i)) && (i !== xs || e in t) || c(t, e, i)
        }
        function w(t, e, i) {
            var n = t[e];
            Li.call(t, e) && We(n, i) && (i !== xs || e in t) || c(t, e, i)
        }
        function l(t, e) {
            for (var i = t.length; i--; )
                if (We(t[i][0], e))
                    return i;
            return -1
        }
        function u(t, n, r, o) {
            return In(t, function(t, e, i) {
                n(o, t, r(t), i)
            }),
            o
        }
        function b(t, e) {
            return t && Et(e, hi(e), t)
        }
        function c(t, e, i) {
            "__proto__" == e && rn ? rn(t, e, {
                configurable: !0,
                enumerable: !0,
                value: i,
                writable: !0
            }) : t[e] = i
        }
        function f(t, e) {
            for (var i = -1, n = null == t, r = e.length, o = $i(r); ++i < r; )
                o[i] = n ? xs : ui(t, e[i]);
            return o
        }
        function x(t, e, i) {
            return t == t && (i !== xs && (t = t <= i ? t : i),
            e !== xs && (t = e <= t ? t : e)),
            t
        }
        function T(i, n, r, o, t, e, s) {
            var a, l, u, c, h, f;
            if (o && (a = e ? o(i, t, e, s) : o(i)),
            a !== xs)
                return a;
            if (!Ze(i))
                return i;
            if (t = Lr(i)) {
                if (h = (c = i).length,
                f = c.constructor(h),
                h && "string" == typeof c[0] && Li.call(c, "index") && (f.index = c.index,
                f.input = c.input),
                a = f,
                !n)
                    return At(i, a)
            } else {
                var d = _(i)
                  , p = "[object Function]" == d || "[object GeneratorFunction]" == d;
                if (Wr(i))
                    return Tt(i, n);
                if ("[object Object]" == d || "[object Arguments]" == d || p && !e) {
                    if (a = se(p ? {} : i),
                    !n)
                        return u = b(a, l = i),
                        Et(l, Xn(l), u)
                } else {
                    if (!fa[d])
                        return e ? i : {};
                    a = function(t, e, i, n) {
                        var r = t.constructor;
                        switch (e) {
                        case "[object ArrayBuffer]":
                            return Ct(t);
                        case "[object Boolean]":
                        case "[object Date]":
                            return new r(+t);
                        case "[object DataView]":
                            return e = n ? Ct(t.buffer) : t.buffer,
                            new t.constructor(e,t.byteOffset,t.byteLength);
                        case "[object Float32Array]":
                        case "[object Float64Array]":
                        case "[object Int8Array]":
                        case "[object Int16Array]":
                        case "[object Int32Array]":
                        case "[object Uint8Array]":
                        case "[object Uint8ClampedArray]":
                        case "[object Uint16Array]":
                        case "[object Uint32Array]":
                            return kt(t, n);
                        case "[object Map]":
                            return Qo(e = n ? i(_s(t), !0) : _s(t), Bo, new t.constructor);
                        case "[object Number]":
                        case "[object String]":
                            return new r(t);
                        case "[object RegExp]":
                            return (e = new t.constructor(t.source,Zs.exec(t))).lastIndex = t.lastIndex,
                            e;
                        case "[object Set]":
                            return Qo(e = n ? i(ys(t), !0) : ys(t), Lo, new t.constructor);
                        case "[object Symbol]":
                            return jn ? Oi(jn.call(t)) : {}
                        }
                    }(i, d, T, n)
                }
            }
            if (s || (s = new v),
            e = s.get(i))
                return e;
            s.set(i, a);
            var g = t ? xs : (r ? function(t) {
                return j(t, hi, Xn)
            }
            : hi)(i);
            return Ho(g || i, function(t, e) {
                g && (t = i[e = t]),
                w(a, e, T(t, n, r, o, e, i, s))
            }),
            a
        }
        function C(t, e, i) {
            var n = i.length;
            if (null == t)
                return !n;
            for (t = Oi(t); n--; ) {
                var r = i[n]
                  , o = e[r]
                  , s = t[r];
                if (s === xs && !(r in t) || !o(s))
                    return !1
            }
            return !0
        }
        function k(t, e, i) {
            if ("function" != typeof t)
                throw new ji("Expected a function");
            return Jn(function() {
                t.apply(xs, i)
            }, e)
        }
        function S(t, e, i, n) {
            var r = -1
              , o = Xo
              , s = !0
              , a = t.length
              , l = []
              , u = e.length;
            if (!a)
                return l;
            i && (e = Yo(e, cs(i))),
            n ? (o = Go,
            s = !1) : 200 <= e.length && (o = fs,
            s = !1,
            e = new m(e));
            t: for (; ++r < a; ) {
                var c = t[r]
                  , h = i ? i(c) : c;
                c = n || 0 !== c ? c : 0;
                if (s && h == h) {
                    for (var f = u; f--; )
                        if (e[f] === h)
                            continue t;
                    l.push(c)
                } else
                    o(e, h, n) || l.push(c)
            }
            return l
        }
        function $(t, n) {
            var r = !0;
            return In(t, function(t, e, i) {
                return r = !!n(t, e, i)
            }),
            r
        }
        function D(t, e, i) {
            for (var n = -1, r = t.length; ++n < r; ) {
                var o = t[n]
                  , s = e(o);
                if (null != s && (a === xs ? s == s && !ei(s) : i(s, a)))
                    var a = s
                      , l = o
            }
            return l
        }
        function A(t, n) {
            var r = [];
            return In(t, function(t, e, i) {
                n(t, e, i) && r.push(t)
            }),
            r
        }
        function E(t, e, i, n, r) {
            var o = -1
              , s = t.length;
            for (i || (i = ae),
            r || (r = []); ++o < s; ) {
                var a = t[o];
                0 < e && i(a) ? 1 < e ? E(a, e - 1, i, n, r) : Zo(r, a) : n || (r[r.length] = a)
            }
            return r
        }
        function P(t, e) {
            return t && Bn(t, e, hi)
        }
        function O(t, e) {
            return t && Ln(t, e, hi)
        }
        function R(e, t) {
            return Vo(t, function(t) {
                return Xe(e[t])
            })
        }
        function M(t, e) {
            for (var i = 0, n = (e = ce(e, t) ? [e] : bt(e)).length; null != t && i < n; )
                t = t[we(e[i++])];
            return i && i == n ? t : xs
        }
        function j(t, e, i) {
            return e = e(t),
            Lr(t) ? e : Zo(e, i(t))
        }
        function z(t, e) {
            return e < t
        }
        function F(t, e) {
            return null != t && Li.call(t, e)
        }
        function I(t, e) {
            return null != t && e in Oi(t)
        }
        function N(t, e, i) {
            for (var n = i ? Go : Xo, r = t[0].length, o = t.length, s = o, a = $i(o), l = 1 / 0, u = []; s--; ) {
                var c = t[s];
                s && e && (c = Yo(c, cs(e))),
                l = _n(c.length, l),
                a[s] = !i && (e || 120 <= r && 120 <= c.length) ? new m(s && c) : xs
            }
            c = t[0];
            var h = -1
              , f = a[0];
            t: for (; ++h < r && u.length < l; ) {
                var d = c[h]
                  , p = e ? e(d) : d;
                d = i || 0 !== d ? d : 0;
                if (f ? !fs(f, p) : !n(u, p, i)) {
                    for (s = o; --s; ) {
                        var g = a[s];
                        if (g ? !fs(g, p) : !n(t[s], p, i))
                            continue t
                    }
                    f && f.push(p),
                    u.push(d)
                }
            }
            return u
        }
        function B(t, e, i) {
            return ce(e, t) || (t = _e(t, e = bt(e)),
            e = De(e)),
            null == (e = null == t ? t : t[we(e)]) ? xs : qo(e, t, i)
        }
        function L(t) {
            return Qe(t) && "[object Arguments]" == Hi.call(t)
        }
        function q(t, e, i, n, r) {
            if (t === e)
                e = !0;
            else if (null == t || null == e || !Ze(t) && !Qe(e))
                e = t != t && e != e;
            else
                t: {
                    var o = Lr(t)
                      , s = Lr(e)
                      , a = "[object Array]"
                      , l = "[object Array]";
                    o || (a = "[object Arguments]" == (a = _(t)) ? "[object Object]" : a),
                    s || (l = "[object Arguments]" == (l = _(e)) ? "[object Object]" : l);
                    var u = "[object Object]" == a;
                    s = "[object Object]" == l;
                    if ((l = a == l) && Wr(t)) {
                        if (!Wr(e)) {
                            e = !1;
                            break t
                        }
                        u = !(o = !0)
                    }
                    if (l && !u)
                        r || (r = new v),
                        e = o || Gr(t) ? Qt(t, e, q, i, n, r) : function(t, e, i, n, r, o, s) {
                            switch (i) {
                            case "[object DataView]":
                                if (t.byteLength != e.byteLength || t.byteOffset != e.byteOffset)
                                    break;
                                t = t.buffer,
                                e = e.buffer;
                            case "[object ArrayBuffer]":
                                if (t.byteLength != e.byteLength || !n(new Yi(t), new Yi(e)))
                                    break;
                                return !0;
                            case "[object Boolean]":
                            case "[object Date]":
                            case "[object Number]":
                                return We(+t, +e);
                            case "[object Error]":
                                return t.name == e.name && t.message == e.message;
                            case "[object RegExp]":
                            case "[object String]":
                                return t == e + "";
                            case "[object Map]":
                                var a = _s;
                            case "[object Set]":
                                if (a || (a = ys),
                                t.size != e.size && !(2 & o))
                                    break;
                                return (i = s.get(t)) ? i == e : (o |= 1,
                                s.set(t, e),
                                e = Qt(a(t), a(e), n, r, o, s),
                                s.delete(t),
                                e);
                            case "[object Symbol]":
                                if (jn)
                                    return jn.call(t) == jn.call(e)
                            }
                            return !1
                        }(t, e, a, q, i, n, r);
                    else {
                        if (!(2 & n) && (o = u && Li.call(t, "__wrapped__"),
                        a = s && Li.call(e, "__wrapped__"),
                        o || a)) {
                            t = o ? t.value() : t,
                            e = a ? e.value() : e,
                            r || (r = new v),
                            e = q(t, e, i, n, r);
                            break t
                        }
                        if (l)
                            e: if (r || (r = new v),
                            o = 2 & n,
                            a = hi(t),
                            s = a.length,
                            l = hi(e).length,
                            s == l || o) {
                                for (u = s; u--; ) {
                                    var c = a[u];
                                    if (!(o ? c in e : Li.call(e, c))) {
                                        e = !1;
                                        break e
                                    }
                                }
                                if ((l = r.get(t)) && r.get(e))
                                    e = l == e;
                                else {
                                    l = !0,
                                    r.set(t, e),
                                    r.set(e, t);
                                    for (var h = o; ++u < s; ) {
                                        var f = t[c = a[u]]
                                          , d = e[c];
                                        if (i)
                                            var p = o ? i(d, f, c, e, t, r) : i(f, d, c, t, e, r);
                                        if (p === xs ? f !== d && !q(f, d, i, n, r) : !p) {
                                            l = !1;
                                            break
                                        }
                                        h || (h = "constructor" == c)
                                    }
                                    l && !h && ((i = t.constructor) != (n = e.constructor) && "constructor"in t && "constructor"in e && !("function" == typeof i && i instanceof i && "function" == typeof n && n instanceof n) && (l = !1)),
                                    r.delete(t),
                                    r.delete(e),
                                    e = l
                                }
                            } else
                                e = !1;
                        else
                            e = !1
                    }
                }
            return e
        }
        function W(t, e, i, n) {
            var r = i.length
              , o = r
              , s = !n;
            if (null == t)
                return !o;
            for (t = Oi(t); r--; ) {
                var a = i[r];
                if (s && a[2] ? a[1] !== t[a[0]] : !(a[0]in t))
                    return !1
            }
            for (; ++r < o; ) {
                var l = (a = i[r])[0]
                  , u = t[l]
                  , c = a[1];
                if (s && a[2]) {
                    if (u === xs && !(l in t))
                        return !1
                } else {
                    if (a = new v,
                    n)
                        var h = n(u, c, l, t, e, a);
                    if (h === xs ? !q(c, u, n, 3, a) : !h)
                        return !1
                }
            }
            return !0
        }
        function H(t) {
            return !(!Ze(t) || Ni && Ni in t) && (Xe(t) ? Vi : Js).test(be(t))
        }
        function U(t) {
            return "function" == typeof t ? t : null == t ? yi : "object" == typeof t ? Lr(t) ? Z(t[0], t[1]) : Y(t) : Ti(t)
        }
        function V(t) {
            if (!fe(t))
                return pn(t);
            var e, i = [];
            for (e in Oi(t))
                Li.call(t, e) && "constructor" != e && i.push(e);
            return i
        }
        function X(t, e) {
            return t < e
        }
        function G(t, n) {
            var r = -1
              , o = He(t) ? $i(t.length) : [];
            return In(t, function(t, e, i) {
                o[++r] = n(t, e, i)
            }),
            o
        }
        function Y(e) {
            var i = ne(e);
            return 1 == i.length && i[0][2] ? de(i[0][0], i[0][1]) : function(t) {
                return t === e || W(t, e, i)
            }
        }
        function Z(i, n) {
            return ce(i) && n == n && !Ze(n) ? de(we(i), n) : function(t) {
                var e = ui(t, i);
                return e === xs && e === n ? ci(t, i) : q(n, e, xs, 3)
            }
        }
        function Q(c, h, f, d, p) {
            c !== h && Bn(h, function(t, e) {
                if (Ze(t)) {
                    p || (p = new v);
                    var i = p
                      , n = c[e]
                      , r = h[e];
                    if (u = i.get(r))
                        y(c, e, u);
                    else {
                        var o = (u = d ? d(n, r, e + "", c, h, i) : xs) === xs;
                        if (o) {
                            var s = Lr(r)
                              , a = !s && Wr(r)
                              , l = !s && !a && Gr(r)
                              , u = r;
                            s || a || l ? u = Lr(n) ? n : Ue(n) ? At(n) : a ? Tt(r, !(o = !1)) : l ? kt(r, !(o = !1)) : [] : Je(r) || Br(r) ? Br(u = n) ? u = ai(n) : (!Ze(n) || f && Xe(n)) && (u = se(r)) : o = !1
                        }
                        o && (i.set(r, u),
                        Q(u, r, f, d, i),
                        i.delete(r)),
                        y(c, e, u)
                    }
                } else
                    (i = d ? d(c[e], t, e + "", c, h, p) : xs) === xs && (i = t),
                    y(c, e, i)
            }, fi)
        }
        function K(t, e) {
            var i = t.length;
            if (i)
                return le(e += e < 0 ? i : 0, i) ? t[e] : xs
        }
        function J(t, i, l) {
            var n = -1;
            return i = Yo(i.length ? i : [yi], cs(ee())),
            function(t, e) {
                var i = t.length;
                for (t.sort(e); i--; )
                    t[i] = t[i].c;
                return t
            }(t = G(t, function(e) {
                return {
                    a: Yo(i, function(t) {
                        return t(e)
                    }),
                    b: ++n,
                    c: e
                }
            }), function(t, e) {
                var i;
                t: {
                    i = -1;
                    for (var n = t.a, r = e.a, o = n.length, s = l.length; ++i < o; ) {
                        var a = St(n[i], r[i]);
                        if (a) {
                            i = s <= i ? a : a * ("desc" == l[i] ? -1 : 1);
                            break t
                        }
                    }
                    i = t.b - e.b
                }
                return i
            })
        }
        function tt(i, t) {
            return et(i = Oi(i), t, function(t, e) {
                return e in i
            })
        }
        function et(t, e, i) {
            for (var n = -1, r = e.length, o = {}; ++n < r; ) {
                var s = e[n]
                  , a = t[s];
                i(a, s) && c(o, s, a)
            }
            return o
        }
        function it(t, e, i, n) {
            var r = n ? ns : is
              , o = -1
              , s = e.length
              , a = t;
            for (t === e && (e = At(e)),
            i && (a = Yo(t, cs(i))); ++o < s; ) {
                var l = 0
                  , u = e[o];
                for (u = i ? i(u) : u; -1 < (l = r(a, u, l, n)); )
                    a !== t && en.call(a, l, 1),
                    en.call(t, l, 1)
            }
            return t
        }
        function nt(t, e) {
            for (var i = t ? e.length : 0, n = i - 1; i--; ) {
                var r = e[i];
                if (i == n || r !== o) {
                    var o = r;
                    if (le(r))
                        en.call(t, r, 1);
                    else if (ce(r, t))
                        delete t[we(r)];
                    else {
                        var s = _e(t, r = bt(r));
                        null != s && delete s[we(De(r))]
                    }
                }
            }
        }
        function rt(t, e) {
            return t + un(yn() * (e - t + 1))
        }
        function ot(t, e) {
            var i = "";
            if (!t || e < 1 || 9007199254740991 < e)
                return i;
            for (; e % 2 && (i += t),
            (e = un(e / 2)) && (t += t),
            e; )
                ;
            return i
        }
        function st(t, e) {
            return tr(ge(t, e, yi), t + "")
        }
        function at(t, e, i, n) {
            if (!Ze(t))
                return t;
            for (var r = -1, o = (e = ce(e, t) ? [e] : bt(e)).length, s = o - 1, a = t; null != a && ++r < o; ) {
                var l = we(e[r])
                  , u = i;
                if (r != s) {
                    var c = a[l];
                    (u = n ? n(c, l, a) : xs) === xs && (u = Ze(c) ? c : le(e[r + 1]) ? [] : {})
                }
                w(a, l, u),
                a = a[l]
            }
            return t
        }
        function lt(t, e, i) {
            var n = -1
              , r = t.length;
            for (e < 0 && (e = r < -e ? 0 : r + e),
            (i = r < i ? r : i) < 0 && (i += r),
            r = i < e ? 0 : i - e >>> 0,
            e >>>= 0,
            i = $i(r); ++n < r; )
                i[n] = t[n + e];
            return i
        }
        function ut(t, n) {
            var r;
            return In(t, function(t, e, i) {
                return !(r = n(t, e, i))
            }),
            !!r
        }
        function ct(t, e, i) {
            var n = 0
              , r = t ? t.length : n;
            if ("number" == typeof e && e == e && r <= 2147483647) {
                for (; n < r; ) {
                    var o = n + r >>> 1
                      , s = t[o];
                    null !== s && !ei(s) && (i ? s <= e : s < e) ? n = o + 1 : r = o
                }
                return r
            }
            return ht(t, e, yi, i)
        }
        function ht(t, e, i, n) {
            e = i(e);
            for (var r = 0, o = t ? t.length : 0, s = e != e, a = null === e, l = ei(e), u = e === xs; r < o; ) {
                var c = un((r + o) / 2)
                  , h = i(t[c])
                  , f = h !== xs
                  , d = null === h
                  , p = h == h
                  , g = ei(h);
                (s ? n || p : u ? p && (n || f) : a ? p && f && (n || !d) : l ? p && f && !d && (n || !g) : !d && !g && (n ? h <= e : h < e)) ? r = c + 1 : o = c
            }
            return _n(o, 4294967294)
        }
        function ft(t, e) {
            for (var i = -1, n = t.length, r = 0, o = []; ++i < n; ) {
                var s = t[i]
                  , a = e ? e(s) : s;
                if (!i || !We(a, l)) {
                    var l = a;
                    o[r++] = 0 === s ? 0 : s
                }
            }
            return o
        }
        function dt(t) {
            return "number" == typeof t ? t : ei(t) ? Ts : +t
        }
        function pt(t) {
            if ("string" == typeof t)
                return t;
            if (Lr(t))
                return Yo(t, pt) + "";
            if (ei(t))
                return zn ? zn.call(t) : "";
            var e = t + "";
            return "0" == e && 1 / t == -1 / 0 ? "-0" : e
        }
        function gt(t, e, i) {
            var n = -1
              , r = Xo
              , o = t.length
              , s = !0
              , a = []
              , l = a;
            if (i)
                s = !1,
                r = Go;
            else if (200 <= o) {
                if (r = e ? null : Un(t))
                    return ys(r);
                s = !1,
                r = fs,
                l = new m
            } else
                l = e ? [] : a;
            t: for (; ++n < o; ) {
                var u = t[n]
                  , c = e ? e(u) : u;
                u = i || 0 !== u ? u : 0;
                if (s && c == c) {
                    for (var h = l.length; h--; )
                        if (l[h] === c)
                            continue t;
                    e && l.push(c),
                    a.push(u)
                } else
                    r(l, c, i) || (l !== a && l.push(c),
                    a.push(u))
            }
            return a
        }
        function _t(t, e, i, n) {
            for (var r = t.length, o = n ? r : -1; (n ? o-- : ++o < r) && e(t[o], o, t); )
                ;
            return i ? lt(t, n ? 0 : o, n ? o + 1 : r) : lt(t, n ? o + 1 : 0, n ? r : o)
        }
        function mt(t, e) {
            var i = t;
            return i instanceof g && (i = i.value()),
            Qo(e, function(t, e) {
                return e.func.apply(e.thisArg, Zo([t], e.args))
            }, i)
        }
        function vt(t, e, i) {
            for (var n = -1, r = t.length; ++n < r; )
                var o = o ? Zo(S(o, t[n], e, i), S(t[n], o, e, i)) : t[n];
            return o && o.length ? gt(o, e, i) : []
        }
        function yt(t, e, i) {
            for (var n = -1, r = t.length, o = e.length, s = {}; ++n < r; )
                i(s, t[n], n < o ? e[n] : xs);
            return s
        }
        function wt(t) {
            return Ue(t) ? t : []
        }
        function bt(t) {
            return Lr(t) ? t : er(t)
        }
        function xt(t, e, i) {
            var n = t.length;
            return i = i === xs ? n : i,
            !e && n <= i ? t : lt(t, e, i)
        }
        function Tt(t, e) {
            if (e)
                return t.slice();
            var i = t.length;
            i = Zi ? Zi(i) : new t.constructor(i);
            return t.copy(i),
            i
        }
        function Ct(t) {
            var e = new t.constructor(t.byteLength);
            return new Yi(e).set(new Yi(t)),
            e
        }
        function kt(t, e) {
            return new t.constructor(e ? Ct(t.buffer) : t.buffer,t.byteOffset,t.length)
        }
        function St(t, e) {
            if (t !== e) {
                var i = t !== xs
                  , n = null === t
                  , r = t == t
                  , o = ei(t)
                  , s = e !== xs
                  , a = null === e
                  , l = e == e
                  , u = ei(e);
                if (!a && !u && !o && e < t || o && s && l && !a && !u || n && s && l || !i && l || !r)
                    return 1;
                if (!n && !o && !u && t < e || u && i && r && !n && !o || a && i && r || !s && r || !l)
                    return -1
            }
            return 0
        }
        function $t(t, e, i, n) {
            var r = -1
              , o = t.length
              , s = i.length
              , a = -1
              , l = e.length
              , u = gn(o - s, 0)
              , c = $i(l + u);
            for (n = !n; ++a < l; )
                c[a] = e[a];
            for (; ++r < s; )
                (n || r < o) && (c[i[r]] = t[r]);
            for (; u--; )
                c[a++] = t[r++];
            return c
        }
        function Dt(t, e, i, n) {
            var r = -1
              , o = t.length
              , s = -1
              , a = i.length
              , l = -1
              , u = e.length
              , c = gn(o - a, 0)
              , h = $i(c + u);
            for (n = !n; ++r < c; )
                h[r] = t[r];
            for (c = r; ++l < u; )
                h[c + l] = e[l];
            for (; ++s < a; )
                (n || r < o) && (h[c + i[s]] = t[r++]);
            return h
        }
        function At(t, e) {
            var i = -1
              , n = t.length;
            for (e || (e = $i(n)); ++i < n; )
                e[i] = t[i];
            return e
        }
        function Et(t, e, i, n) {
            var r = !i;
            i || (i = {});
            for (var o = -1, s = e.length; ++o < s; ) {
                var a = e[o]
                  , l = n ? n(i[a], t[a], a, i, t) : xs;
                l === xs && (l = t[a]),
                r ? c(i, a, l) : w(i, a, l)
            }
            return i
        }
        function Pt(r, o) {
            return function(t, e) {
                var i = Lr(t) ? Wo : u
                  , n = o ? o() : {};
                return i(t, r, ee(e, 2), n)
            }
        }
        function Ot(s) {
            return st(function(t, e) {
                var i = -1
                  , n = e.length
                  , r = 1 < n ? e[n - 1] : xs
                  , o = 2 < n ? e[2] : xs;
                r = 3 < s.length && "function" == typeof r ? (n--,
                r) : xs;
                for (o && ue(e[0], e[1], o) && (r = n < 3 ? xs : r,
                n = 1),
                t = Oi(t); ++i < n; )
                    (o = e[i]) && s(t, o, i, r);
                return t
            })
        }
        function Rt(o, s) {
            return function(t, e) {
                if (null == t)
                    return t;
                if (!He(t))
                    return o(t, e);
                for (var i = t.length, n = s ? i : -1, r = Oi(t); (s ? n-- : ++n < i) && !1 !== e(r[n], n, r); )
                    ;
                return t
            }
        }
        function Mt(a) {
            return function(t, e, i) {
                for (var n = -1, r = Oi(t), o = (i = i(t)).length; o--; ) {
                    var s = i[a ? o : ++n];
                    if (!1 === e(r[s], s, r))
                        break
                }
                return t
            }
        }
        function jt(n) {
            return function(t) {
                t = li(t);
                var e = la.test(t) ? bs(t) : xs
                  , i = e ? e[0] : t.charAt(0);
                return t = e ? xt(e, 1).join("") : t.slice(1),
                i[n]() + t
            }
        }
        function zt(e) {
            return function(t) {
                return Qo(mi(_i(t).replace(oa, "")), e, "")
            }
        }
        function Ft(i) {
            return function() {
                switch ((t = arguments).length) {
                case 0:
                    return new i;
                case 1:
                    return new i(t[0]);
                case 2:
                    return new i(t[0],t[1]);
                case 3:
                    return new i(t[0],t[1],t[2]);
                case 4:
                    return new i(t[0],t[1],t[2],t[3]);
                case 5:
                    return new i(t[0],t[1],t[2],t[3],t[4]);
                case 6:
                    return new i(t[0],t[1],t[2],t[3],t[4],t[5]);
                case 7:
                    return new i(t[0],t[1],t[2],t[3],t[4],t[5],t[6])
                }
                var t, e = Fn(i.prototype);
                return Ze(t = i.apply(e, t)) ? t : e
            }
        }
        function It(o) {
            return function(t, e, i) {
                var n = Oi(t);
                if (!He(t)) {
                    var r = ee(e, 3);
                    t = hi(t),
                    e = function(t) {
                        return r(n[t], t, n)
                    }
                }
                return -1 < (e = o(t, e, i)) ? n[r ? t[e] : e] : xs
            }
        }
        function Nt(a) {
            return Kt(function(n) {
                var r = n.length
                  , t = r
                  , e = p.prototype.thru;
                for (a && n.reverse(); t--; ) {
                    if ("function" != typeof (i = n[t]))
                        throw new ji("Expected a function");
                    if (e && !o && "wrapper" == Jt(i))
                        var o = new p([],!0)
                }
                for (t = o ? t : r; ++t < r; ) {
                    var i, s = "wrapper" == (e = Jt(i = n[t])) ? Vn(i) : xs;
                    o = s && he(s[0]) && 424 == s[1] && !s[4].length && 1 == s[9] ? o[Jt(s[0])].apply(o, s[3]) : 1 == i.length && he(i) ? o[e]() : o.thru(i)
                }
                return function() {
                    var t = (i = arguments)[0];
                    if (o && 1 == i.length && Lr(t) && 200 <= t.length)
                        return o.plant(t).value();
                    for (var e = 0, i = r ? n[e].apply(this, i) : t; ++e < r; )
                        i = n[e].call(this, i);
                    return i
                }
            })
        }
        function Bt(u, c, h, f, d, p, g, _, m, v) {
            var y = 128 & c
              , w = 1 & c
              , b = 2 & c
              , x = 24 & c
              , T = 512 & c
              , C = b ? xs : Ft(u);
            return function t() {
                for (var e = arguments.length, i = $i(e), n = e; n--; )
                    i[n] = arguments[n];
                if (x) {
                    var r, o = te(t);
                    for (n = i.length,
                    r = 0; n--; )
                        i[n] === o && ++r
                }
                if (f && (i = $t(i, f, d, x)),
                p && (i = Dt(i, p, g, x)),
                e -= r,
                x && e < v)
                    return o = vs(i, o),
                    Xt(u, c, Bt, t.placeholder, h, i, o, _, m, v - e);
                if (o = w ? h : this,
                n = b ? o[u] : u,
                e = i.length,
                _) {
                    r = i.length;
                    for (var s = _n(_.length, r), a = At(i); s--; ) {
                        var l = _[s];
                        i[s] = le(l, r) ? a[l] : xs
                    }
                } else
                    T && 1 < e && i.reverse();
                return y && m < e && (i.length = m),
                this && this !== ga && this instanceof t && (n = C || Ft(n)),
                n.apply(o, i)
            }
        }
        function Lt(s, a) {
            return function(t, e) {
                return i = t,
                n = s,
                r = a(e),
                o = {},
                P(i, function(t, e, i) {
                    n(o, r(t), e, i)
                }),
                o;
                var i, n, r, o
            }
        }
        function qt(n, r) {
            return function(t, e) {
                var i;
                if (t === xs && e === xs)
                    return r;
                if (t !== xs && (i = t),
                e !== xs) {
                    if (i === xs)
                        return e;
                    e = "string" == typeof t || "string" == typeof e ? (t = pt(t),
                    pt(e)) : (t = dt(t),
                    dt(e)),
                    i = n(t, e)
                }
                return i
            }
        }
        function Wt(n) {
            return Kt(function(t) {
                return t = Yo(t, cs(ee())),
                st(function(e) {
                    var i = this;
                    return n(t, function(t) {
                        return qo(t, i, e)
                    })
                })
            })
        }
        function Ht(t, e) {
            var i = (e = e === xs ? " " : pt(e)).length;
            return i < 2 ? i ? ot(e, t) : e : (i = ot(e, ln(t / ws(e))),
            la.test(e) ? xt(bs(i), 0, t).join("") : i.slice(0, t))
        }
        function Ut(o) {
            return function(t, e, i) {
                i && "number" != typeof i && ue(t, e, i) && (e = i = xs),
                t = ni(t),
                e === xs ? (e = t,
                t = 0) : e = ni(e),
                i = i === xs ? t < e ? 1 : -1 : ni(i);
                var n = -1;
                e = gn(ln((e - t) / (i || 1)), 0);
                for (var r = $i(e); e--; )
                    r[o ? e : ++n] = t,
                    t += i;
                return r
            }
        }
        function Vt(i) {
            return function(t, e) {
                return "string" == typeof t && "string" == typeof e || (t = si(t),
                e = si(e)),
                i(t, e)
            }
        }
        function Xt(t, e, i, n, r, o, s, a, l, u) {
            var c = 8 & e;
            return 4 & (e = (e | (c ? 32 : 64)) & ~(c ? 64 : 32)) || (e &= -4),
            r = [t, e, r, c ? o : xs, c ? s : xs, o = c ? xs : o, s = c ? xs : s, a, l, u],
            i = i.apply(xs, r),
            he(t) && Kn(i, r),
            i.placeholder = n,
            me(i, t, e)
        }
        function Gt(t) {
            var n = Pi[t];
            return function(t, e) {
                if (t = si(t),
                e = _n(ri(e), 292)) {
                    var i = (li(t) + "e").split("e");
                    return +((i = (li(i = n(i[0] + "e" + (+i[1] + e))) + "e").split("e"))[0] + "e" + (+i[1] - e))
                }
                return n(t)
            }
        }
        function Yt(s) {
            return function(t) {
                var e, i, n, r, o = _(t);
                return "[object Map]" == o ? _s(t) : "[object Set]" == o ? (i = t,
                n = -1,
                r = Array(i.size),
                i.forEach(function(t) {
                    r[++n] = [t, t]
                }),
                r) : Yo(s(e = t), function(t) {
                    return [t, e[t]]
                })
            }
        }
        function Zt(t, e, i, n, r, o, s, a) {
            var l = 2 & e;
            if (!l && "function" != typeof t)
                throw new ji("Expected a function");
            var u = n ? n.length : 0;
            if (u || (e &= -97,
            n = r = xs),
            s = s === xs ? s : gn(ri(s), 0),
            a = a === xs ? a : ri(a),
            u -= r ? r.length : 0,
            64 & e) {
                var c = n
                  , h = r;
                n = r = xs
            }
            var f, d, p, g, _, m, v, y, w, b, x, T, C, k = l ? xs : Vn(t);
            return o = [t, e, i, n, r, c, h, o, s, a],
            k && (e = (i = o[1]) | (t = k[1]),
            n = 128 == t && 8 == i || 128 == t && 256 == i && o[7].length <= k[8] || 384 == t && k[7].length <= k[8] && 8 == i,
            e < 131 || n) && (1 & t && (o[2] = k[2],
            e |= 1 & i ? 0 : 4),
            (i = k[3]) && (n = o[3],
            o[3] = n ? $t(n, i, k[4]) : i,
            o[4] = n ? vs(o[3], "__lodash_placeholder__") : k[4]),
            (i = k[5]) && (n = o[5],
            o[5] = n ? Dt(n, i, k[6]) : i,
            o[6] = n ? vs(o[5], "__lodash_placeholder__") : k[6]),
            (i = k[7]) && (o[7] = i),
            128 & t && (o[8] = null == o[8] ? k[8] : _n(o[8], k[8])),
            null == o[9] && (o[9] = k[9]),
            o[0] = k[0],
            o[1] = e),
            t = o[0],
            e = o[1],
            i = o[2],
            n = o[3],
            r = o[4],
            !(a = o[9] = null == o[9] ? l ? 0 : t.length : gn(o[9] - u, 0)) && 24 & e && (e &= -25),
            me((k ? qn : Kn)(e && 1 != e ? 8 == e || 16 == e ? (x = e,
            T = a,
            C = Ft(b = t),
            function t() {
                for (var e = arguments.length, i = $i(e), n = e, r = te(t); n--; )
                    i[n] = arguments[n];
                return (e -= (n = e < 3 && i[0] !== r && i[e - 1] !== r ? [] : vs(i, r)).length) < T ? Xt(b, x, Bt, t.placeholder, xs, i, n, xs, xs, T - e) : qo(this && this !== ga && this instanceof t ? C : b, this, i)
            }
            ) : 32 != e && 33 != e || r.length ? Bt.apply(xs, o) : (m = i,
            v = n,
            y = 1 & e,
            w = Ft(_ = t),
            function t() {
                for (var e = -1, i = arguments.length, n = -1, r = v.length, o = $i(r + i), s = this && this !== ga && this instanceof t ? w : _; ++n < r; )
                    o[n] = v[n];
                for (; i--; )
                    o[n++] = arguments[++e];
                return qo(s, y ? m : this, o)
            }
            ) : (d = i,
            p = 1 & e,
            g = Ft(f = t),
            function t() {
                return (this && this !== ga && this instanceof t ? g : f).apply(p ? d : this, arguments)
            }
            ), o), t, e)
        }
        function Qt(t, e, i, n, r, o) {
            var s = 2 & r
              , a = t.length;
            if (a != (l = e.length) && !(s && a < l))
                return !1;
            if ((l = o.get(t)) && o.get(e))
                return l == e;
            var l = -1
              , u = !0
              , c = 1 & r ? new m : xs;
            for (o.set(t, e),
            o.set(e, t); ++l < a; ) {
                var h = t[l]
                  , f = e[l];
                if (n)
                    var d = s ? n(f, h, l, e, t, o) : n(h, f, l, t, e, o);
                if (d !== xs) {
                    if (d)
                        continue;
                    u = !1;
                    break
                }
                if (c) {
                    if (!Jo(e, function(t, e) {
                        if (!fs(c, e) && (h === t || i(h, t, n, r, o)))
                            return c.push(e)
                    })) {
                        u = !1;
                        break
                    }
                } else if (h !== f && !i(h, f, n, r, o)) {
                    u = !1;
                    break
                }
            }
            return o.delete(t),
            o.delete(e),
            u
        }
        function Kt(t) {
            return tr(ge(t, xs, Se), t + "")
        }
        function Jt(t) {
            for (var e = t.name + "", i = Dn[e], n = Li.call(Dn, e) ? i.length : 0; n--; ) {
                var r = i[n]
                  , o = r.func;
                if (null == o || o == t)
                    return r.name
            }
            return e
        }
        function te(t) {
            return (Li.call(d, "placeholder") ? d : t).placeholder
        }
        function ee() {
            var t = (t = d.iteratee || wi) === wi ? U : t;
            return arguments.length ? t(arguments[0], arguments[1]) : t
        }
        function ie(t, e) {
            var i = t.__data__
              , n = typeof e;
            return ("string" == n || "number" == n || "symbol" == n || "boolean" == n ? "__proto__" !== e : null === e) ? i["string" == typeof e ? "string" : "hash"] : i.map
        }
        function ne(t) {
            for (var e = hi(t), i = e.length; i--; ) {
                var n = e[i]
                  , r = t[n];
                e[i] = [n, r, r == r && !Ze(r)]
            }
            return e
        }
        function re(t, e) {
            var i = null == t ? xs : t[e];
            return H(i) ? i : xs
        }
        function oe(t, e, i) {
            for (var n = -1, r = (e = ce(e, t) ? [e] : bt(e)).length, o = !1; ++n < r; ) {
                var s = we(e[n]);
                if (!(o = null != t && i(t, s)))
                    break;
                t = t[s]
            }
            return o || ++n != r ? o : !!(r = t ? t.length : 0) && Ye(r) && le(s, r) && (Lr(t) || Br(t))
        }
        function se(t) {
            return "function" != typeof t.constructor || fe(t) ? {} : Fn(Qi(t))
        }
        function ae(t) {
            return Lr(t) || Br(t) || !!(nn && t && t[nn])
        }
        function le(t, e) {
            return !!(e = null == e ? 9007199254740991 : e) && ("number" == typeof t || ea.test(t)) && -1 < t && 0 == t % 1 && t < e
        }
        function ue(t, e, i) {
            if (!Ze(i))
                return !1;
            var n = typeof e;
            return !!("number" == n ? He(i) && le(e, i.length) : "string" == n && e in i) && We(i[e], t)
        }
        function ce(t, e) {
            if (Lr(t))
                return !1;
            var i = typeof t;
            return !("number" != i && "symbol" != i && "boolean" != i && null != t && !ei(t)) || zs.test(t) || !js.test(t) || null != e && t in Oi(e)
        }
        function he(t) {
            var e = Jt(t)
              , i = d[e];
            return "function" == typeof i && e in g.prototype && (t === i || !!(e = Vn(i)) && t === e[0])
        }
        function fe(t) {
            var e = t && t.constructor;
            return t === ("function" == typeof e && e.prototype || Fi)
        }
        function de(e, i) {
            return function(t) {
                return null != t && t[e] === i && (i !== xs || e in Oi(t))
            }
        }
        function pe(t, e, i, n, r, o) {
            return Ze(t) && Ze(e) && (o.set(e, t),
            Q(t, e, xs, pe, o),
            o.delete(e)),
            t
        }
        function ge(r, o, s) {
            return o = gn(o === xs ? r.length - 1 : o, 0),
            function() {
                for (var t = arguments, e = -1, i = gn(t.length - o, 0), n = $i(i); ++e < i; )
                    n[e] = t[o + e];
                for (e = -1,
                i = $i(o + 1); ++e < o; )
                    i[e] = t[e];
                return i[o] = s(n),
                qo(r, this, i)
            }
        }
        function _e(t, e) {
            return 1 == e.length ? t : M(t, lt(e, 0, -1))
        }
        function me(t, e, i) {
            var n = e + "";
            e = tr;
            var r, o = xe;
            return (o = (i = o(r = (r = n.match(Us)) ? r[1].split(Vs) : [], i)).length) && (i[r = o - 1] = (1 < o ? "& " : "") + i[r],
            i = i.join(2 < o ? ", " : " "),
            n = n.replace(Hs, "{\n/* [wrapped with " + i + "] */\n")),
            e(t, n)
        }
        function ve(i) {
            var n = 0
              , r = 0;
            return function() {
                var t = mn()
                  , e = 16 - (t - r);
                if (r = t,
                0 < e) {
                    if (500 <= ++n)
                        return arguments[0]
                } else
                    n = 0;
                return i.apply(xs, arguments)
            }
        }
        function ye(t, e) {
            var i = -1
              , n = (r = t.length) - 1;
            for (e = e === xs ? r : e; ++i < e; ) {
                var r, o = t[r = rt(i, n)];
                t[r] = t[i],
                t[i] = o
            }
            return t.length = e,
            t
        }
        function we(t) {
            if ("string" == typeof t || ei(t))
                return t;
            var e = t + "";
            return "0" == e && 1 / t == -1 / 0 ? "-0" : e
        }
        function be(t) {
            if (null == t)
                return "";
            try {
                return Bi.call(t)
            } catch (t) {}
            return t + ""
        }
        function xe(i, n) {
            return Ho(Cs, function(t) {
                var e = "_." + t[0];
                n & t[1] && !Xo(i, e) && i.push(e)
            }),
            i.sort()
        }
        function Te(t) {
            if (t instanceof g)
                return t.clone();
            var e = new p(t.__wrapped__,t.__chain__);
            return e.__actions__ = At(t.__actions__),
            e.__index__ = t.__index__,
            e.__values__ = t.__values__,
            e
        }
        function Ce(t, e, i) {
            var n = t ? t.length : 0;
            return n ? ((i = null == i ? 0 : ri(i)) < 0 && (i = gn(n + i, 0)),
            es(t, ee(e, 3), i)) : -1
        }
        function ke(t, e, i) {
            var n = t ? t.length : 0;
            if (!n)
                return -1;
            var r = n - 1;
            return i !== xs && (r = ri(i),
            r = i < 0 ? gn(n + r, 0) : _n(r, n - 1)),
            es(t, ee(e, 3), r, !0)
        }
        function Se(t) {
            return t && t.length ? E(t, 1) : []
        }
        function $e(t) {
            return t && t.length ? t[0] : xs
        }
        function De(t) {
            var e = t ? t.length : 0;
            return e ? t[e - 1] : xs
        }
        function Ae(t, e) {
            return t && t.length && e && e.length ? it(t, e) : t
        }
        function Ee(t) {
            return t ? wn.call(t) : t
        }
        function Pe(e) {
            if (!e || !e.length)
                return [];
            var i = 0;
            return e = Vo(e, function(t) {
                if (Ue(t))
                    return i = gn(t.length, i),
                    !0
            }),
            us(i, function(t) {
                return Yo(e, ss(t))
            })
        }
        function Oe(t, e) {
            if (!t || !t.length)
                return [];
            var i = Pe(t);
            return null == e ? i : Yo(i, function(t) {
                return qo(e, xs, t)
            })
        }
        function Re(t) {
            return (t = d(t)).__chain__ = !0,
            t
        }
        function Me(t, e) {
            return e(t)
        }
        function je(t, e) {
            return (Lr(t) ? Ho : In)(t, ee(e, 3))
        }
        function ze(t, e) {
            return (Lr(t) ? function(t, e) {
                for (var i = t ? t.length : 0; i-- && !1 !== e(t[i], i, t); )
                    ;
                return t
            }
            : Nn)(t, ee(e, 3))
        }
        function Fe(t, e) {
            return (Lr(t) ? Yo : G)(t, ee(e, 3))
        }
        function Ie(t, e, i) {
            return e = i ? xs : e,
            e = t && null == e ? t.length : e,
            Zt(t, 128, xs, xs, xs, xs, e)
        }
        function Ne(t, e) {
            var i;
            if ("function" != typeof e)
                throw new ji("Expected a function");
            return t = ri(t),
            function() {
                return 0 < --t && (i = e.apply(this, arguments)),
                t <= 1 && (e = xs),
                i
            }
        }
        function Be(n, r, t) {
            function i(t) {
                var e = l
                  , i = u;
                return l = u = xs,
                p = t,
                h = n.apply(i, e)
            }
            function o(t) {
                var e = t - d;
                return t -= p,
                d === xs || r <= e || e < 0 || _ && c <= t
            }
            function s() {
                var t = Dr();
                if (o(t))
                    return a(t);
                var e, i = Jn;
                e = t - p,
                t = r - (t - d),
                e = _ ? _n(t, c - e) : t,
                f = i(s, e)
            }
            function a(t) {
                return f = xs,
                m && l ? i(t) : (l = u = xs,
                h)
            }
            function e() {
                var t = Dr()
                  , e = o(t);
                if (l = arguments,
                u = this,
                d = t,
                e) {
                    if (f === xs)
                        return p = t = d,
                        f = Jn(s, r),
                        g ? i(t) : h;
                    if (_)
                        return f = Jn(s, r),
                        i(d)
                }
                return f === xs && (f = Jn(s, r)),
                h
            }
            var l, u, c, h, f, d, p = 0, g = !1, _ = !1, m = !0;
            if ("function" != typeof n)
                throw new ji("Expected a function");
            return r = si(r) || 0,
            Ze(t) && (g = !!t.leading,
            c = (_ = "maxWait"in t) ? gn(si(t.maxWait) || 0, r) : c,
            m = "trailing"in t ? !!t.trailing : m),
            e.cancel = function() {
                f !== xs && Hn(f),
                p = 0,
                l = d = u = f = xs
            }
            ,
            e.flush = function() {
                return f === xs ? h : a(Dr())
            }
            ,
            e
        }
        function Le(n, r) {
            function o() {
                var t = arguments
                  , e = r ? r.apply(this, t) : t[0]
                  , i = o.cache;
                return i.has(e) ? i.get(e) : (t = n.apply(this, t),
                o.cache = i.set(e, t) || i,
                t)
            }
            if ("function" != typeof n || r && "function" != typeof r)
                throw new ji("Expected a function");
            return o.cache = new (Le.Cache || s),
            o
        }
        function qe(e) {
            if ("function" != typeof e)
                throw new ji("Expected a function");
            return function() {
                var t = arguments;
                switch (t.length) {
                case 0:
                    return !e.call(this);
                case 1:
                    return !e.call(this, t[0]);
                case 2:
                    return !e.call(this, t[0], t[1]);
                case 3:
                    return !e.call(this, t[0], t[1], t[2])
                }
                return !e.apply(this, t)
            }
        }
        function We(t, e) {
            return t === e || t != t && e != e
        }
        function He(t) {
            return null != t && Ye(t.length) && !Xe(t)
        }
        function Ue(t) {
            return Qe(t) && He(t)
        }
        function Ve(t) {
            return !!Qe(t) && ("[object Error]" == Hi.call(t) || "string" == typeof t.message && "string" == typeof t.name)
        }
        function Xe(t) {
            return "[object Function]" == (t = Ze(t) ? Hi.call(t) : "") || "[object GeneratorFunction]" == t || "[object Proxy]" == t
        }
        function Ge(t) {
            return "number" == typeof t && t == ri(t)
        }
        function Ye(t) {
            return "number" == typeof t && -1 < t && 0 == t % 1 && t <= 9007199254740991
        }
        function Ze(t) {
            var e = typeof t;
            return null != t && ("object" == e || "function" == e)
        }
        function Qe(t) {
            return null != t && "object" == typeof t
        }
        function Ke(t) {
            return "number" == typeof t || Qe(t) && "[object Number]" == Hi.call(t)
        }
        function Je(t) {
            return !(!Qe(t) || "[object Object]" != Hi.call(t)) && (null === (t = Qi(t)) || "function" == typeof (t = Li.call(t, "constructor") && t.constructor) && t instanceof t && Bi.call(t) == Wi)
        }
        function ti(t) {
            return "string" == typeof t || !Lr(t) && Qe(t) && "[object String]" == Hi.call(t)
        }
        function ei(t) {
            return "symbol" == typeof t || Qe(t) && "[object Symbol]" == Hi.call(t)
        }
        function ii(t) {
            if (!t)
                return [];
            if (He(t))
                return ti(t) ? bs(t) : At(t);
            if (Ki && t[Ki]) {
                t = t[Ki]();
                for (var e, i = []; !(e = t.next()).done; )
                    i.push(e.value);
                return i
            }
            return ("[object Map]" == (e = _(t)) ? _s : "[object Set]" == e ? ys : pi)(t)
        }
        function ni(t) {
            return t ? (t = si(t)) === 1 / 0 || t === -1 / 0 ? 17976931348623157e292 * (t < 0 ? -1 : 1) : t == t ? t : 0 : 0 === t ? t : 0
        }
        function ri(t) {
            var e = (t = ni(t)) % 1;
            return t == t ? e ? t - e : t : 0
        }
        function oi(t) {
            return t ? x(ri(t), 0, 4294967295) : 0
        }
        function si(t) {
            if ("number" == typeof t)
                return t;
            if (ei(t))
                return Ts;
            if (Ze(t) && (t = Ze(t = "function" == typeof t.valueOf ? t.valueOf() : t) ? t + "" : t),
            "string" != typeof t)
                return 0 === t ? t : +t;
            t = t.replace(Ls, "");
            var e = Ks.test(t);
            return e || ta.test(t) ? pa(t.slice(2), e ? 2 : 8) : Qs.test(t) ? Ts : +t
        }
        function ai(t) {
            return Et(t, fi(t))
        }
        function li(t) {
            return null == t ? "" : pt(t)
        }
        function ui(t, e, i) {
            return (t = null == t ? xs : M(t, e)) === xs ? i : t
        }
        function ci(t, e) {
            return null != t && oe(t, e, I)
        }
        function hi(t) {
            return He(t) ? a(t) : V(t)
        }
        function fi(t) {
            if (He(t))
                t = a(t, !0);
            else if (Ze(t)) {
                var e, i = fe(t), n = [];
                for (e in t)
                    ("constructor" != e || !i && Li.call(t, e)) && n.push(e);
                t = n
            } else {
                if (e = [],
                null != t)
                    for (i in Oi(t))
                        e.push(i);
                t = e
            }
            return t
        }
        function di(t, e) {
            return null == t ? {} : et(t, j(t, fi, Gn), ee(e))
        }
        function pi(t) {
            return t ? hs(t, hi(t)) : []
        }
        function gi(t) {
            return bo(li(t).toLowerCase())
        }
        function _i(t) {
            return (t = li(t)) && t.replace(ia, Ta).replace(sa, "")
        }
        function mi(t, e, i) {
            return t = li(t),
            (e = i ? xs : e) === xs ? ua.test(t) ? t.match(aa) || [] : t.match(Xs) || [] : t.match(e) || []
        }
        function vi(t) {
            return function() {
                return t
            }
        }
        function yi(t) {
            return t
        }
        function wi(t) {
            return U("function" == typeof t ? t : T(t, !0))
        }
        function bi(n, e, t) {
            var i = hi(e)
              , r = R(e, i);
            null != t || Ze(e) && (r.length || !i.length) || (t = e,
            e = n,
            n = this,
            r = R(e, hi(e)));
            var o = !(Ze(t) && "chain"in t && !t.chain)
              , s = Xe(n);
            return Ho(r, function(t) {
                var i = e[t];
                n[t] = i,
                s && (n.prototype[t] = function() {
                    var t = this.__chain__;
                    if (o || t) {
                        var e = n(this.__wrapped__);
                        return (e.__actions__ = At(this.__actions__)).push({
                            func: i,
                            args: arguments,
                            thisArg: n
                        }),
                        e.__chain__ = t,
                        e
                    }
                    return i.apply(n, Zo([this.value()], arguments))
                }
                )
            }),
            n
        }
        function xi() {}
        function Ti(t) {
            return ce(t) ? ss(we(t)) : (e = t,
            function(t) {
                return M(t, e)
            }
            );
            var e
        }
        function Ci() {
            return []
        }
        function ki() {
            return !1
        }
        var Si, $i = (e = e ? Sa.defaults(ga.Object(), e, Sa.pick(ga, ca)) : ga).Array, Di = e.Date, Ai = e.Error, Ei = e.Function, Pi = e.Math, Oi = e.Object, Ri = e.RegExp, Mi = e.String, ji = e.TypeError, zi = $i.prototype, Fi = Oi.prototype, Ii = e["__core-js_shared__"], Ni = (Si = /[^.]+$/.exec(Ii && Ii.keys && Ii.keys.IE_PROTO || "")) ? "Symbol(src)_1." + Si : "", Bi = Ei.prototype.toString, Li = Fi.hasOwnProperty, qi = 0, Wi = Bi.call(Oi), Hi = Fi.toString, Ui = ga._, Vi = Ri("^" + Bi.call(Li).replace(Ns, "\\$&").replace(/hasOwnProperty|(function).*?(?=\\\()| for .+?(?=\\\])/g, "$1.*?") + "$"), Xi = _a ? e.Buffer : xs, Gi = e.Symbol, Yi = e.Uint8Array, Zi = Xi ? Xi.f : xs, Qi = ms(Oi.getPrototypeOf, Oi), Ki = Gi ? Gi.iterator : xs, Ji = Oi.create, tn = Fi.propertyIsEnumerable, en = zi.splice, nn = Gi ? Gi.isConcatSpreadable : xs, rn = function() {
            try {
                var t = re(Oi, "defineProperty");
                return t({}, "", {}),
                t
            } catch (t) {}
        }(), on = e.clearTimeout !== ga.clearTimeout && e.clearTimeout, sn = Di && Di.now !== ga.Date.now && Di.now, an = e.setTimeout !== ga.setTimeout && e.setTimeout, ln = Pi.ceil, un = Pi.floor, cn = Oi.getOwnPropertySymbols, hn = Xi ? Xi.isBuffer : xs, fn = e.isFinite, dn = zi.join, pn = ms(Oi.keys, Oi), gn = Pi.max, _n = Pi.min, mn = Di.now, vn = e.parseInt, yn = Pi.random, wn = zi.reverse, bn = re(e, "DataView"), xn = re(e, "Map"), Tn = re(e, "Promise"), Cn = re(e, "Set"), kn = re(e, "WeakMap"), Sn = re(Oi, "create"), $n = kn && new kn, Dn = {}, An = be(bn), En = be(xn), Pn = be(Tn), On = be(Cn), Rn = be(kn), Mn = Gi ? Gi.prototype : xs, jn = Mn ? Mn.valueOf : xs, zn = Mn ? Mn.toString : xs, Fn = function() {
            function e() {}
            return function(t) {
                return Ze(t) ? Ji ? Ji(t) : (e.prototype = t,
                t = new e,
                e.prototype = xs,
                t) : {}
            }
        }();
        d.templateSettings = {
            escape: Os,
            evaluate: Rs,
            interpolate: Ms,
            variable: "",
            imports: {
                _: d
            }
        },
        (d.prototype = o.prototype).constructor = d,
        (p.prototype = Fn(o.prototype)).constructor = p,
        (g.prototype = Fn(o.prototype)).constructor = g,
        i.prototype.clear = function() {
            this.__data__ = Sn ? Sn(null) : {},
            this.size = 0
        }
        ,
        i.prototype.delete = function(t) {
            return t = this.has(t) && delete this.__data__[t],
            this.size -= t ? 1 : 0,
            t
        }
        ,
        i.prototype.get = function(t) {
            var e = this.__data__;
            return Sn ? "__lodash_hash_undefined__" === (t = e[t]) ? xs : t : Li.call(e, t) ? e[t] : xs
        }
        ,
        i.prototype.has = function(t) {
            var e = this.__data__;
            return Sn ? e[t] !== xs : Li.call(e, t)
        }
        ,
        i.prototype.set = function(t, e) {
            var i = this.__data__;
            return this.size += this.has(t) ? 0 : 1,
            i[t] = Sn && e === xs ? "__lodash_hash_undefined__" : e,
            this
        }
        ,
        r.prototype.clear = function() {
            this.__data__ = [],
            this.size = 0
        }
        ,
        r.prototype.delete = function(t) {
            var e = this.__data__;
            return !((t = l(e, t)) < 0 || (t == e.length - 1 ? e.pop() : en.call(e, t, 1),
            --this.size,
            0))
        }
        ,
        r.prototype.get = function(t) {
            var e = this.__data__;
            return (t = l(e, t)) < 0 ? xs : e[t][1]
        }
        ,
        r.prototype.has = function(t) {
            return -1 < l(this.__data__, t)
        }
        ,
        r.prototype.set = function(t, e) {
            var i = this.__data__
              , n = l(i, t);
            return n < 0 ? (++this.size,
            i.push([t, e])) : i[n][1] = e,
            this
        }
        ,
        s.prototype.clear = function() {
            this.size = 0,
            this.__data__ = {
                hash: new i,
                map: new (xn || r),
                string: new i
            }
        }
        ,
        s.prototype.delete = function(t) {
            return t = ie(this, t).delete(t),
            this.size -= t ? 1 : 0,
            t
        }
        ,
        s.prototype.get = function(t) {
            return ie(this, t).get(t)
        }
        ,
        s.prototype.has = function(t) {
            return ie(this, t).has(t)
        }
        ,
        s.prototype.set = function(t, e) {
            var i = ie(this, t)
              , n = i.size;
            return i.set(t, e),
            this.size += i.size == n ? 0 : 1,
            this
        }
        ,
        m.prototype.add = m.prototype.push = function(t) {
            return this.__data__.set(t, "__lodash_hash_undefined__"),
            this
        }
        ,
        m.prototype.has = function(t) {
            return this.__data__.has(t)
        }
        ,
        v.prototype.clear = function() {
            this.__data__ = new r,
            this.size = 0
        }
        ,
        v.prototype.delete = function(t) {
            var e = this.__data__;
            return t = e.delete(t),
            this.size = e.size,
            t
        }
        ,
        v.prototype.get = function(t) {
            return this.__data__.get(t)
        }
        ,
        v.prototype.has = function(t) {
            return this.__data__.has(t)
        }
        ,
        v.prototype.set = function(t, e) {
            var i = this.__data__;
            if (i instanceof r) {
                var n = i.__data__;
                if (!xn || n.length < 199)
                    return n.push([t, e]),
                    this.size = ++i.size,
                    this;
                i = this.__data__ = new s(n)
            }
            return i.set(t, e),
            this.size = i.size,
            this
        }
        ;
        var In = Rt(P)
          , Nn = Rt(O, !0)
          , Bn = Mt()
          , Ln = Mt(!0)
          , qn = $n ? function(t, e) {
            return $n.set(t, e),
            t
        }
        : yi
          , Wn = rn ? function(t, e) {
            return rn(t, "toString", {
                configurable: !0,
                enumerable: !1,
                value: vi(e),
                writable: !0
            })
        }
        : yi
          , Hn = on || function(t) {
            return ga.clearTimeout(t)
        }
          , Un = Cn && 1 / ys(new Cn([, -0]))[1] == 1 / 0 ? function(t) {
            return new Cn(t)
        }
        : xi
          , Vn = $n ? function(t) {
            return $n.get(t)
        }
        : xi
          , Xn = cn ? ms(cn, Oi) : Ci
          , Gn = cn ? function(t) {
            for (var e = []; t; )
                Zo(e, Xn(t)),
                t = Qi(t);
            return e
        }
        : Ci;
        (bn && "[object DataView]" != _(new bn(new ArrayBuffer(1))) || xn && "[object Map]" != _(new xn) || Tn && "[object Promise]" != _(Tn.resolve()) || Cn && "[object Set]" != _(new Cn) || kn && "[object WeakMap]" != _(new kn)) && (_ = function(t) {
            var e = Hi.call(t);
            if (t = (t = "[object Object]" == e ? t.constructor : xs) ? be(t) : xs)
                switch (t) {
                case An:
                    return "[object DataView]";
                case En:
                    return "[object Map]";
                case Pn:
                    return "[object Promise]";
                case On:
                    return "[object Set]";
                case Rn:
                    return "[object WeakMap]"
                }
            return e
        }
        );
        var Yn, Zn, Qn = Ii ? Xe : ki, Kn = ve(qn), Jn = an || function(t, e) {
            return ga.setTimeout(t, e)
        }
        , tr = ve(Wn), er = (Zn = (Yn = Le(Yn = function(t) {
            t = li(t);
            var r = [];
            return Fs.test(t) && r.push(""),
            t.replace(Is, function(t, e, i, n) {
                r.push(i ? n.replace(Gs, "$1") : e || t)
            }),
            r
        }
        , function(t) {
            return 500 === Zn.size && Zn.clear(),
            t
        })).cache,
        Yn), ir = st(function(t, e) {
            return Ue(t) ? S(t, E(e, 1, Ue, !0)) : []
        }), nr = st(function(t, e) {
            var i = De(e);
            return Ue(i) && (i = xs),
            Ue(t) ? S(t, E(e, 1, Ue, !0), ee(i, 2)) : []
        }), rr = st(function(t, e) {
            var i = De(e);
            return Ue(i) && (i = xs),
            Ue(t) ? S(t, E(e, 1, Ue, !0), xs, i) : []
        }), or = st(function(t) {
            var e = Yo(t, wt);
            return e.length && e[0] === t[0] ? N(e) : []
        }), sr = st(function(t) {
            var e = De(t)
              , i = Yo(t, wt);
            return e === De(i) ? e = xs : i.pop(),
            i.length && i[0] === t[0] ? N(i, ee(e, 2)) : []
        }), ar = st(function(t) {
            var e = De(t)
              , i = Yo(t, wt);
            return e === De(i) ? e = xs : i.pop(),
            i.length && i[0] === t[0] ? N(i, xs, e) : []
        }), lr = st(Ae), ur = Kt(function(t, e) {
            var i = t ? t.length : 0
              , n = f(t, e);
            return nt(t, Yo(e, function(t) {
                return le(t, i) ? +t : t
            }).sort(St)),
            n
        }), cr = st(function(t) {
            return gt(E(t, 1, Ue, !0))
        }), hr = st(function(t) {
            var e = De(t);
            return Ue(e) && (e = xs),
            gt(E(t, 1, Ue, !0), ee(e, 2))
        }), fr = st(function(t) {
            var e = De(t);
            return Ue(e) && (e = xs),
            gt(E(t, 1, Ue, !0), xs, e)
        }), dr = st(function(t, e) {
            return Ue(t) ? S(t, e) : []
        }), pr = st(function(t) {
            return vt(Vo(t, Ue))
        }), gr = st(function(t) {
            var e = De(t);
            return Ue(e) && (e = xs),
            vt(Vo(t, Ue), ee(e, 2))
        }), _r = st(function(t) {
            var e = De(t);
            return Ue(e) && (e = xs),
            vt(Vo(t, Ue), xs, e)
        }), mr = st(Pe), vr = st(function(t) {
            var e;
            return Oe(t, e = "function" == typeof (e = 1 < (e = t.length) ? t[e - 1] : xs) ? (t.pop(),
            e) : xs)
        }), yr = Kt(function(e) {
            function t(t) {
                return f(t, e)
            }
            var i = e.length
              , n = i ? e[0] : 0
              , r = this.__wrapped__;
            return !(1 < i || this.__actions__.length) && r instanceof g && le(n) ? ((r = r.slice(n, +n + (i ? 1 : 0))).__actions__.push({
                func: Me,
                args: [t],
                thisArg: xs
            }),
            new p(r,this.__chain__).thru(function(t) {
                return i && !t.length && t.push(xs),
                t
            })) : this.thru(t)
        }), wr = Pt(function(t, e, i) {
            Li.call(t, i) ? ++t[i] : c(t, i, 1)
        }), br = It(Ce), xr = It(ke), Tr = Pt(function(t, e, i) {
            Li.call(t, i) ? t[i].push(e) : c(t, i, [e])
        }), Cr = st(function(t, i, n) {
            var r = -1
              , o = "function" == typeof i
              , s = ce(i)
              , a = He(t) ? $i(t.length) : [];
            return In(t, function(t) {
                var e = o ? i : s && null != t ? t[i] : xs;
                a[++r] = e ? qo(e, t, n) : B(t, i, n)
            }),
            a
        }), kr = Pt(function(t, e, i) {
            c(t, i, e)
        }), Sr = Pt(function(t, e, i) {
            t[i ? 0 : 1].push(e)
        }, function() {
            return [[], []]
        }), $r = st(function(t, e) {
            if (null == t)
                return [];
            var i = e.length;
            return 1 < i && ue(t, e[0], e[1]) ? e = [] : 2 < i && ue(e[0], e[1], e[2]) && (e = [e[0]]),
            J(t, E(e, 1), [])
        }), Dr = sn || function() {
            return ga.Date.now()
        }
        , Ar = st(function(t, e, i) {
            var n = 1;
            if (i.length) {
                var r = vs(i, te(Ar));
                n = 32 | n
            }
            return Zt(t, n, e, i, r)
        }), Er = st(function(t, e, i) {
            var n = 3;
            if (i.length) {
                var r = vs(i, te(Er));
                n = 32 | n
            }
            return Zt(e, n, t, i, r)
        }), Pr = st(function(t, e) {
            return k(t, 1, e)
        }), Or = st(function(t, e, i) {
            return k(t, si(e) || 0, i)
        });
        Le.Cache = s;
        var Rr, Mr = st(function(n, r) {
            var o = (r = 1 == r.length && Lr(r[0]) ? Yo(r[0], cs(ee())) : Yo(E(r, 1), cs(ee()))).length;
            return st(function(t) {
                for (var e = -1, i = _n(t.length, o); ++e < i; )
                    t[e] = r[e].call(this, t[e]);
                return qo(n, this, t)
            })
        }), jr = st(function(t, e) {
            return Zt(t, 32, xs, e, vs(e, te(jr)))
        }), zr = st(function(t, e) {
            return Zt(t, 64, xs, e, vs(e, te(zr)))
        }), Fr = Kt(function(t, e) {
            return Zt(t, 256, xs, xs, xs, e)
        }), Ir = Vt(z), Nr = Vt(function(t, e) {
            return e <= t
        }), Br = L(function() {
            return arguments
        }()) ? L : function(t) {
            return Qe(t) && Li.call(t, "callee") && !tn.call(t, "callee")
        }
        , Lr = $i.isArray, qr = ma ? cs(ma) : function(t) {
            return Qe(t) && "[object ArrayBuffer]" == Hi.call(t)
        }
        , Wr = hn || ki, Hr = va ? cs(va) : function(t) {
            return Qe(t) && "[object Date]" == Hi.call(t)
        }
        , Ur = ya ? cs(ya) : function(t) {
            return Qe(t) && "[object Map]" == _(t)
        }
        , Vr = wa ? cs(wa) : function(t) {
            return Ze(t) && "[object RegExp]" == Hi.call(t)
        }
        , Xr = ba ? cs(ba) : function(t) {
            return Qe(t) && "[object Set]" == _(t)
        }
        , Gr = xa ? cs(xa) : function(t) {
            return Qe(t) && Ye(t.length) && !!ha[Hi.call(t)]
        }
        , Yr = Vt(X), Zr = Vt(function(t, e) {
            return t <= e
        }), Qr = Ot(function(t, e) {
            if (fe(e) || He(e))
                Et(e, hi(e), t);
            else
                for (var i in e)
                    Li.call(e, i) && w(t, i, e[i])
        }), Kr = Ot(function(t, e) {
            Et(e, fi(e), t)
        }), Jr = Ot(function(t, e, i, n) {
            Et(e, fi(e), t, n)
        }), to = Ot(function(t, e, i, n) {
            Et(e, hi(e), t, n)
        }), eo = Kt(f), io = st(function(t) {
            return t.push(xs, h),
            qo(Jr, xs, t)
        }), no = st(function(t) {
            return t.push(xs, pe),
            qo(lo, xs, t)
        }), ro = Lt(function(t, e, i) {
            t[e] = i
        }, vi(yi)), oo = Lt(function(t, e, i) {
            Li.call(t, e) ? t[e].push(i) : t[e] = [i]
        }, ee), so = st(B), ao = Ot(function(t, e, i) {
            Q(t, e, i)
        }), lo = Ot(function(t, e, i, n) {
            Q(t, e, i, n)
        }), uo = Kt(function(t, e) {
            return null == t ? {} : (e = Yo(e, we),
            tt(t, S(j(t, fi, Gn), e)))
        }), co = Kt(function(t, e) {
            return null == t ? {} : tt(t, Yo(e, we))
        }), ho = Yt(hi), fo = Yt(fi), po = zt(function(t, e, i) {
            return e = e.toLowerCase(),
            t + (i ? gi(e) : e)
        }), go = zt(function(t, e, i) {
            return t + (i ? "-" : "") + e.toLowerCase()
        }), _o = zt(function(t, e, i) {
            return t + (i ? " " : "") + e.toLowerCase()
        }), mo = jt("toLowerCase"), vo = zt(function(t, e, i) {
            return t + (i ? "_" : "") + e.toLowerCase()
        }), yo = zt(function(t, e, i) {
            return t + (i ? " " : "") + bo(e)
        }), wo = zt(function(t, e, i) {
            return t + (i ? " " : "") + e.toUpperCase()
        }), bo = jt("toUpperCase"), xo = st(function(t, e) {
            try {
                return qo(t, xs, e)
            } catch (t) {
                return Ve(t) ? t : new Ai(t)
            }
        }), To = Kt(function(e, t) {
            return Ho(t, function(t) {
                t = we(t),
                c(e, t, Ar(e[t], e))
            }),
            e
        }), Co = Nt(), ko = Nt(!0), So = st(function(e, i) {
            return function(t) {
                return B(t, e, i)
            }
        }), $o = st(function(e, i) {
            return function(t) {
                return B(e, t, i)
            }
        }), Do = Wt(Yo), Ao = Wt(Uo), Eo = Wt(Jo), Po = Ut(), Oo = Ut(!0), Ro = qt(function(t, e) {
            return t + e
        }, 0), Mo = Gt("ceil"), jo = qt(function(t, e) {
            return t / e
        }, 1), zo = Gt("floor"), Fo = qt(function(t, e) {
            return t * e
        }, 1), Io = Gt("round"), No = qt(function(t, e) {
            return t - e
        }, 0);
        return d.after = function(t, e) {
            if ("function" != typeof e)
                throw new ji("Expected a function");
            return t = ri(t),
            function() {
                if (--t < 1)
                    return e.apply(this, arguments)
            }
        }
        ,
        d.ary = Ie,
        d.assign = Qr,
        d.assignIn = Kr,
        d.assignInWith = Jr,
        d.assignWith = to,
        d.at = eo,
        d.before = Ne,
        d.bind = Ar,
        d.bindAll = To,
        d.bindKey = Er,
        d.castArray = function() {
            if (!arguments.length)
                return [];
            var t = arguments[0];
            return Lr(t) ? t : [t]
        }
        ,
        d.chain = Re,
        d.chunk = function(t, e, i) {
            if (e = (i ? ue(t, e, i) : e === xs) ? 1 : gn(ri(e), 0),
            !(i = t ? t.length : 0) || e < 1)
                return [];
            for (var n = 0, r = 0, o = $i(ln(i / e)); n < i; )
                o[r++] = lt(t, n, n += e);
            return o
        }
        ,
        d.compact = function(t) {
            for (var e = -1, i = t ? t.length : 0, n = 0, r = []; ++e < i; ) {
                var o = t[e];
                o && (r[n++] = o)
            }
            return r
        }
        ,
        d.concat = function() {
            var t = arguments.length;
            if (!t)
                return [];
            for (var e = $i(t - 1), i = arguments[0]; t--; )
                e[t - 1] = arguments[t];
            return Zo(Lr(i) ? At(i) : [i], E(e, 1))
        }
        ,
        d.cond = function(n) {
            var r = n ? n.length : 0
              , e = ee();
            return n = r ? Yo(n, function(t) {
                if ("function" != typeof t[1])
                    throw new ji("Expected a function");
                return [e(t[0]), t[1]]
            }) : [],
            st(function(t) {
                for (var e = -1; ++e < r; ) {
                    var i = n[e];
                    if (qo(i[0], this, t))
                        return qo(i[1], this, t)
                }
            })
        }
        ,
        d.conforms = function(t) {
            return e = T(t, !0),
            i = hi(e),
            function(t) {
                return C(t, e, i)
            }
            ;
            var e, i
        }
        ,
        d.constant = vi,
        d.countBy = wr,
        d.create = function(t, e) {
            var i = Fn(t);
            return e ? b(i, e) : i
        }
        ,
        d.curry = function t(e, i, n) {
            return (e = Zt(e, 8, xs, xs, xs, xs, xs, i = n ? xs : i)).placeholder = t.placeholder,
            e
        }
        ,
        d.curryRight = function t(e, i, n) {
            return (e = Zt(e, 16, xs, xs, xs, xs, xs, i = n ? xs : i)).placeholder = t.placeholder,
            e
        }
        ,
        d.debounce = Be,
        d.defaults = io,
        d.defaultsDeep = no,
        d.defer = Pr,
        d.delay = Or,
        d.difference = ir,
        d.differenceBy = nr,
        d.differenceWith = rr,
        d.drop = function(t, e, i) {
            var n = t ? t.length : 0;
            return n ? lt(t, (e = i || e === xs ? 1 : ri(e)) < 0 ? 0 : e, n) : []
        }
        ,
        d.dropRight = function(t, e, i) {
            var n = t ? t.length : 0;
            return n ? lt(t, 0, (e = n - (e = i || e === xs ? 1 : ri(e))) < 0 ? 0 : e) : []
        }
        ,
        d.dropRightWhile = function(t, e) {
            return t && t.length ? _t(t, ee(e, 3), !0, !0) : []
        }
        ,
        d.dropWhile = function(t, e) {
            return t && t.length ? _t(t, ee(e, 3), !0) : []
        }
        ,
        d.fill = function(t, e, i, n) {
            var r = t ? t.length : 0;
            if (!r)
                return [];
            for (i && "number" != typeof i && ue(t, e, i) && (i = 0,
            n = r),
            r = t.length,
            (i = ri(i)) < 0 && (i = r < -i ? 0 : r + i),
            (n = n === xs || r < n ? r : ri(n)) < 0 && (n += r),
            n = n < i ? 0 : oi(n); i < n; )
                t[i++] = e;
            return t
        }
        ,
        d.filter = function(t, e) {
            return (Lr(t) ? Vo : A)(t, ee(e, 3))
        }
        ,
        d.flatMap = function(t, e) {
            return E(Fe(t, e), 1)
        }
        ,
        d.flatMapDeep = function(t, e) {
            return E(Fe(t, e), 1 / 0)
        }
        ,
        d.flatMapDepth = function(t, e, i) {
            return i = i === xs ? 1 : ri(i),
            E(Fe(t, e), i)
        }
        ,
        d.flatten = Se,
        d.flattenDeep = function(t) {
            return t && t.length ? E(t, 1 / 0) : []
        }
        ,
        d.flattenDepth = function(t, e) {
            return t && t.length ? E(t, e = e === xs ? 1 : ri(e)) : []
        }
        ,
        d.flip = function(t) {
            return Zt(t, 512)
        }
        ,
        d.flow = Co,
        d.flowRight = ko,
        d.fromPairs = function(t) {
            for (var e = -1, i = t ? t.length : 0, n = {}; ++e < i; ) {
                var r = t[e];
                n[r[0]] = r[1]
            }
            return n
        }
        ,
        d.functions = function(t) {
            return null == t ? [] : R(t, hi(t))
        }
        ,
        d.functionsIn = function(t) {
            return null == t ? [] : R(t, fi(t))
        }
        ,
        d.groupBy = Tr,
        d.initial = function(t) {
            return t && t.length ? lt(t, 0, -1) : []
        }
        ,
        d.intersection = or,
        d.intersectionBy = sr,
        d.intersectionWith = ar,
        d.invert = ro,
        d.invertBy = oo,
        d.invokeMap = Cr,
        d.iteratee = wi,
        d.keyBy = kr,
        d.keys = hi,
        d.keysIn = fi,
        d.map = Fe,
        d.mapKeys = function(t, n) {
            var r = {};
            return n = ee(n, 3),
            P(t, function(t, e, i) {
                c(r, n(t, e, i), t)
            }),
            r
        }
        ,
        d.mapValues = function(t, n) {
            var r = {};
            return n = ee(n, 3),
            P(t, function(t, e, i) {
                c(r, e, n(t, e, i))
            }),
            r
        }
        ,
        d.matches = function(t) {
            return Y(T(t, !0))
        }
        ,
        d.matchesProperty = function(t, e) {
            return Z(t, T(e, !0))
        }
        ,
        d.memoize = Le,
        d.merge = ao,
        d.mergeWith = lo,
        d.method = So,
        d.methodOf = $o,
        d.mixin = bi,
        d.negate = qe,
        d.nthArg = function(e) {
            return e = ri(e),
            st(function(t) {
                return K(t, e)
            })
        }
        ,
        d.omit = uo,
        d.omitBy = function(t, e) {
            return di(t, qe(ee(e)))
        }
        ,
        d.once = function(t) {
            return Ne(2, t)
        }
        ,
        d.orderBy = function(t, e, i, n) {
            return null == t ? [] : (Lr(e) || (e = null == e ? [] : [e]),
            Lr(i = n ? xs : i) || (i = null == i ? [] : [i]),
            J(t, e, i))
        }
        ,
        d.over = Do,
        d.overArgs = Mr,
        d.overEvery = Ao,
        d.overSome = Eo,
        d.partial = jr,
        d.partialRight = zr,
        d.partition = Sr,
        d.pick = co,
        d.pickBy = di,
        d.property = Ti,
        d.propertyOf = function(e) {
            return function(t) {
                return null == e ? xs : M(e, t)
            }
        }
        ,
        d.pull = lr,
        d.pullAll = Ae,
        d.pullAllBy = function(t, e, i) {
            return t && t.length && e && e.length ? it(t, e, ee(i, 2)) : t
        }
        ,
        d.pullAllWith = function(t, e, i) {
            return t && t.length && e && e.length ? it(t, e, xs, i) : t
        }
        ,
        d.pullAt = ur,
        d.range = Po,
        d.rangeRight = Oo,
        d.rearg = Fr,
        d.reject = function(t, e) {
            return (Lr(t) ? Vo : A)(t, qe(ee(e, 3)))
        }
        ,
        d.remove = function(t, e) {
            var i = [];
            if (!t || !t.length)
                return i;
            var n = -1
              , r = []
              , o = t.length;
            for (e = ee(e, 3); ++n < o; ) {
                var s = t[n];
                e(s, n, t) && (i.push(s),
                r.push(n))
            }
            return nt(t, r),
            i
        }
        ,
        d.rest = function(t, e) {
            if ("function" != typeof t)
                throw new ji("Expected a function");
            return st(t, e = e === xs ? e : ri(e))
        }
        ,
        d.reverse = Ee,
        d.sampleSize = function(t, e, i) {
            return e = (i ? ue(t, e, i) : e === xs) ? 1 : ri(e),
            (Lr(t) ? function(t, e) {
                return ye(At(t), x(e, 0, t.length))
            }
            : function(t, e) {
                var i = pi(t);
                return ye(i, x(e, 0, i.length))
            }
            )(t, e)
        }
        ,
        d.set = function(t, e, i) {
            return null == t ? t : at(t, e, i)
        }
        ,
        d.setWith = function(t, e, i, n) {
            return n = "function" == typeof n ? n : xs,
            null == t ? t : at(t, e, i, n)
        }
        ,
        d.shuffle = function(t) {
            return (Lr(t) ? function(t) {
                return ye(At(t))
            }
            : function(t) {
                return ye(pi(t))
            }
            )(t)
        }
        ,
        d.slice = function(t, e, i) {
            var n = t ? t.length : 0;
            return n ? lt(t, e, i = i && "number" != typeof i && ue(t, e, i) ? (e = 0,
            n) : (e = null == e ? 0 : ri(e),
            i === xs ? n : ri(i))) : []
        }
        ,
        d.sortBy = $r,
        d.sortedUniq = function(t) {
            return t && t.length ? ft(t) : []
        }
        ,
        d.sortedUniqBy = function(t, e) {
            return t && t.length ? ft(t, ee(e, 2)) : []
        }
        ,
        d.split = function(t, e, i) {
            return i && "number" != typeof i && ue(t, e, i) && (e = i = xs),
            (i = i === xs ? 4294967295 : i >>> 0) ? (t = li(t)) && ("string" == typeof e || null != e && !Vr(e)) && (!(e = pt(e)) && la.test(t)) ? xt(bs(t), 0, i) : t.split(e, i) : []
        }
        ,
        d.spread = function(i, n) {
            if ("function" != typeof i)
                throw new ji("Expected a function");
            return n = n === xs ? 0 : gn(ri(n), 0),
            st(function(t) {
                var e = t[n];
                return t = xt(t, 0, n),
                e && Zo(t, e),
                qo(i, this, t)
            })
        }
        ,
        d.tail = function(t) {
            var e = t ? t.length : 0;
            return e ? lt(t, 1, e) : []
        }
        ,
        d.take = function(t, e, i) {
            return t && t.length ? lt(t, 0, (e = i || e === xs ? 1 : ri(e)) < 0 ? 0 : e) : []
        }
        ,
        d.takeRight = function(t, e, i) {
            var n = t ? t.length : 0;
            return n ? lt(t, (e = n - (e = i || e === xs ? 1 : ri(e))) < 0 ? 0 : e, n) : []
        }
        ,
        d.takeRightWhile = function(t, e) {
            return t && t.length ? _t(t, ee(e, 3), !1, !0) : []
        }
        ,
        d.takeWhile = function(t, e) {
            return t && t.length ? _t(t, ee(e, 3)) : []
        }
        ,
        d.tap = function(t, e) {
            return e(t),
            t
        }
        ,
        d.throttle = function(t, e, i) {
            var n = !0
              , r = !0;
            if ("function" != typeof t)
                throw new ji("Expected a function");
            return Ze(i) && (n = "leading"in i ? !!i.leading : n,
            r = "trailing"in i ? !!i.trailing : r),
            Be(t, e, {
                leading: n,
                maxWait: e,
                trailing: r
            })
        }
        ,
        d.thru = Me,
        d.toArray = ii,
        d.toPairs = ho,
        d.toPairsIn = fo,
        d.toPath = function(t) {
            return Lr(t) ? Yo(t, we) : ei(t) ? [t] : At(er(t))
        }
        ,
        d.toPlainObject = ai,
        d.transform = function(t, n, r) {
            var e = Lr(t)
              , i = e || Wr(t) || Gr(t);
            if (n = ee(n, 4),
            null == r) {
                var o = t && t.constructor;
                r = i ? e ? new o : [] : Ze(t) && Xe(o) ? Fn(Qi(t)) : {}
            }
            return (i ? Ho : P)(t, function(t, e, i) {
                return n(r, t, e, i)
            }),
            r
        }
        ,
        d.unary = function(t) {
            return Ie(t, 1)
        }
        ,
        d.union = cr,
        d.unionBy = hr,
        d.unionWith = fr,
        d.uniq = function(t) {
            return t && t.length ? gt(t) : []
        }
        ,
        d.uniqBy = function(t, e) {
            return t && t.length ? gt(t, ee(e, 2)) : []
        }
        ,
        d.uniqWith = function(t, e) {
            return t && t.length ? gt(t, xs, e) : []
        }
        ,
        d.unset = function(t, e) {
            var i, n;
            null == t ? i = !0 : (i = _e(i = t, n = ce(n = e, i) ? [n] : bt(n)),
            n = we(De(n)),
            i = !(null != i && Li.call(i, n)) || delete i[n]);
            return i
        }
        ,
        d.unzip = Pe,
        d.unzipWith = Oe,
        d.update = function(t, e, i) {
            return null == t ? t : at(t, e, ("function" == typeof i ? i : yi)(M(t, e)), void 0)
        }
        ,
        d.updateWith = function(t, e, i, n) {
            return n = "function" == typeof n ? n : xs,
            null != t && (t = at(t, e, ("function" == typeof i ? i : yi)(M(t, e)), n)),
            t
        }
        ,
        d.values = pi,
        d.valuesIn = function(t) {
            return null == t ? [] : hs(t, fi(t))
        }
        ,
        d.without = dr,
        d.words = mi,
        d.wrap = function(t, e) {
            return jr(e = null == e ? yi : e, t)
        }
        ,
        d.xor = pr,
        d.xorBy = gr,
        d.xorWith = _r,
        d.zip = mr,
        d.zipObject = function(t, e) {
            return yt(t || [], e || [], w)
        }
        ,
        d.zipObjectDeep = function(t, e) {
            return yt(t || [], e || [], at)
        }
        ,
        d.zipWith = vr,
        d.entries = ho,
        d.entriesIn = fo,
        d.extend = Kr,
        d.extendWith = Jr,
        bi(d, d),
        d.add = Ro,
        d.attempt = xo,
        d.camelCase = po,
        d.capitalize = gi,
        d.ceil = Mo,
        d.clamp = function(t, e, i) {
            return i === xs && (i = e,
            e = xs),
            i !== xs && (i = (i = si(i)) == i ? i : 0),
            e !== xs && (e = (e = si(e)) == e ? e : 0),
            x(si(t), e, i)
        }
        ,
        d.clone = function(t) {
            return T(t, !1, !0)
        }
        ,
        d.cloneDeep = function(t) {
            return T(t, !0, !0)
        }
        ,
        d.cloneDeepWith = function(t, e) {
            return T(t, !0, !0, e)
        }
        ,
        d.cloneWith = function(t, e) {
            return T(t, !1, !0, e)
        }
        ,
        d.conformsTo = function(t, e) {
            return null == e || C(t, e, hi(e))
        }
        ,
        d.deburr = _i,
        d.defaultTo = function(t, e) {
            return null == t || t != t ? e : t
        }
        ,
        d.divide = jo,
        d.endsWith = function(t, e, i) {
            t = li(t),
            e = pt(e);
            var n = t.length;
            n = i = i === xs ? n : x(ri(i), 0, n);
            return 0 <= (i -= e.length) && t.slice(i, n) == e
        }
        ,
        d.eq = We,
        d.escape = function(t) {
            return (t = li(t)) && Ps.test(t) ? t.replace(As, Ca) : t
        }
        ,
        d.escapeRegExp = function(t) {
            return (t = li(t)) && Bs.test(t) ? t.replace(Ns, "\\$&") : t
        }
        ,
        d.every = function(t, e, i) {
            var n = Lr(t) ? Uo : $;
            return i && ue(t, e, i) && (e = xs),
            n(t, ee(e, 3))
        }
        ,
        d.find = br,
        d.findIndex = Ce,
        d.findKey = function(t, e) {
            return ts(t, ee(e, 3), P)
        }
        ,
        d.findLast = xr,
        d.findLastIndex = ke,
        d.findLastKey = function(t, e) {
            return ts(t, ee(e, 3), O)
        }
        ,
        d.floor = zo,
        d.forEach = je,
        d.forEachRight = ze,
        d.forIn = function(t, e) {
            return null == t ? t : Bn(t, ee(e, 3), fi)
        }
        ,
        d.forInRight = function(t, e) {
            return null == t ? t : Ln(t, ee(e, 3), fi)
        }
        ,
        d.forOwn = function(t, e) {
            return t && P(t, ee(e, 3))
        }
        ,
        d.forOwnRight = function(t, e) {
            return t && O(t, ee(e, 3))
        }
        ,
        d.get = ui,
        d.gt = Ir,
        d.gte = Nr,
        d.has = function(t, e) {
            return null != t && oe(t, e, F)
        }
        ,
        d.hasIn = ci,
        d.head = $e,
        d.identity = yi,
        d.includes = function(t, e, i, n) {
            return t = He(t) ? t : pi(t),
            i = i && !n ? ri(i) : 0,
            n = t.length,
            i < 0 && (i = gn(n + i, 0)),
            ti(t) ? i <= n && -1 < t.indexOf(e, i) : !!n && -1 < is(t, e, i)
        }
        ,
        d.indexOf = function(t, e, i) {
            var n = t ? t.length : 0;
            return n ? ((i = null == i ? 0 : ri(i)) < 0 && (i = gn(n + i, 0)),
            is(t, e, i)) : -1
        }
        ,
        d.inRange = function(t, e, i) {
            return e = ni(e),
            i === xs ? (i = e,
            e = 0) : i = ni(i),
            (t = si(t)) >= _n(e, i) && t < gn(e, i)
        }
        ,
        d.invoke = so,
        d.isArguments = Br,
        d.isArray = Lr,
        d.isArrayBuffer = qr,
        d.isArrayLike = He,
        d.isArrayLikeObject = Ue,
        d.isBoolean = function(t) {
            return !0 === t || !1 === t || Qe(t) && "[object Boolean]" == Hi.call(t)
        }
        ,
        d.isBuffer = Wr,
        d.isDate = Hr,
        d.isElement = function(t) {
            return null != t && 1 === t.nodeType && Qe(t) && !Je(t)
        }
        ,
        d.isEmpty = function(t) {
            if (He(t) && (Lr(t) || "string" == typeof t || "function" == typeof t.splice || Wr(t) || Gr(t) || Br(t)))
                return !t.length;
            var e = _(t);
            if ("[object Map]" == e || "[object Set]" == e)
                return !t.size;
            if (fe(t))
                return !V(t).length;
            for (var i in t)
                if (Li.call(t, i))
                    return !1;
            return !0
        }
        ,
        d.isEqual = function(t, e) {
            return q(t, e)
        }
        ,
        d.isEqualWith = function(t, e, i) {
            var n = (i = "function" == typeof i ? i : xs) ? i(t, e) : xs;
            return n === xs ? q(t, e, i) : !!n
        }
        ,
        d.isError = Ve,
        d.isFinite = function(t) {
            return "number" == typeof t && fn(t)
        }
        ,
        d.isFunction = Xe,
        d.isInteger = Ge,
        d.isLength = Ye,
        d.isMap = Ur,
        d.isMatch = function(t, e) {
            return t === e || W(t, e, ne(e))
        }
        ,
        d.isMatchWith = function(t, e, i) {
            return i = "function" == typeof i ? i : xs,
            W(t, e, ne(e), i)
        }
        ,
        d.isNaN = function(t) {
            return Ke(t) && t != +t
        }
        ,
        d.isNative = function(t) {
            if (Qn(t))
                throw new Ai("Unsupported core-js use. Try https://github.com/es-shims.");
            return H(t)
        }
        ,
        d.isNil = function(t) {
            return null == t
        }
        ,
        d.isNull = function(t) {
            return null === t
        }
        ,
        d.isNumber = Ke,
        d.isObject = Ze,
        d.isObjectLike = Qe,
        d.isPlainObject = Je,
        d.isRegExp = Vr,
        d.isSafeInteger = function(t) {
            return Ge(t) && -9007199254740991 <= t && t <= 9007199254740991
        }
        ,
        d.isSet = Xr,
        d.isString = ti,
        d.isSymbol = ei,
        d.isTypedArray = Gr,
        d.isUndefined = function(t) {
            return t === xs
        }
        ,
        d.isWeakMap = function(t) {
            return Qe(t) && "[object WeakMap]" == _(t)
        }
        ,
        d.isWeakSet = function(t) {
            return Qe(t) && "[object WeakSet]" == Hi.call(t)
        }
        ,
        d.join = function(t, e) {
            return t ? dn.call(t, e) : ""
        }
        ,
        d.kebabCase = go,
        d.last = De,
        d.lastIndexOf = function(t, e, i) {
            var n = t ? t.length : 0;
            if (!n)
                return -1;
            var r = n;
            if (i !== xs && (r = (r = ri(i)) < 0 ? gn(n + r, 0) : _n(r, n - 1)),
            e == e) {
                for (i = r + 1; i-- && t[i] !== e; )
                    ;
                t = i
            } else
                t = es(t, rs, r, !0);
            return t
        }
        ,
        d.lowerCase = _o,
        d.lowerFirst = mo,
        d.lt = Yr,
        d.lte = Zr,
        d.max = function(t) {
            return t && t.length ? D(t, yi, z) : xs
        }
        ,
        d.maxBy = function(t, e) {
            return t && t.length ? D(t, ee(e, 2), z) : xs
        }
        ,
        d.mean = function(t) {
            return os(t, yi)
        }
        ,
        d.meanBy = function(t, e) {
            return os(t, ee(e, 2))
        }
        ,
        d.min = function(t) {
            return t && t.length ? D(t, yi, X) : xs
        }
        ,
        d.minBy = function(t, e) {
            return t && t.length ? D(t, ee(e, 2), X) : xs
        }
        ,
        d.stubArray = Ci,
        d.stubFalse = ki,
        d.stubObject = function() {
            return {}
        }
        ,
        d.stubString = function() {
            return ""
        }
        ,
        d.stubTrue = function() {
            return !0
        }
        ,
        d.multiply = Fo,
        d.nth = function(t, e) {
            return t && t.length ? K(t, ri(e)) : xs
        }
        ,
        d.noConflict = function() {
            return ga._ === this && (ga._ = Ui),
            this
        }
        ,
        d.noop = xi,
        d.now = Dr,
        d.pad = function(t, e, i) {
            t = li(t);
            var n = (e = ri(e)) ? ws(t) : 0;
            return !e || e <= n ? t : Ht(un(e = (e - n) / 2), i) + t + Ht(ln(e), i)
        }
        ,
        d.padEnd = function(t, e, i) {
            t = li(t);
            var n = (e = ri(e)) ? ws(t) : 0;
            return e && n < e ? t + Ht(e - n, i) : t
        }
        ,
        d.padStart = function(t, e, i) {
            t = li(t);
            var n = (e = ri(e)) ? ws(t) : 0;
            return e && n < e ? Ht(e - n, i) + t : t
        }
        ,
        d.parseInt = function(t, e, i) {
            return i || null == e ? e = 0 : e && (e = +e),
            vn(li(t).replace(qs, ""), e || 0)
        }
        ,
        d.random = function(t, e, i) {
            if (i && "boolean" != typeof i && ue(t, e, i) && (e = i = xs),
            i === xs && ("boolean" == typeof e ? (i = e,
            e = xs) : "boolean" == typeof t && (i = t,
            t = xs)),
            t === xs && e === xs ? (t = 0,
            e = 1) : (t = ni(t),
            e === xs ? (e = t,
            t = 0) : e = ni(e)),
            e < t) {
                var n = t;
                t = e,
                e = n
            }
            return i || t % 1 || e % 1 ? (i = yn(),
            _n(t + i * (e - t + da("1e-" + ((i + "").length - 1))), e)) : rt(t, e)
        }
        ,
        d.reduce = function(t, e, i) {
            var n = Lr(t) ? Qo : as
              , r = arguments.length < 3;
            return n(t, ee(e, 4), i, r, In)
        }
        ,
        d.reduceRight = function(t, e, i) {
            var n = Lr(t) ? Ko : as
              , r = arguments.length < 3;
            return n(t, ee(e, 4), i, r, Nn)
        }
        ,
        d.repeat = function(t, e, i) {
            return e = (i ? ue(t, e, i) : e === xs) ? 1 : ri(e),
            ot(li(t), e)
        }
        ,
        d.replace = function() {
            var t = arguments
              , e = li(t[0]);
            return t.length < 3 ? e : e.replace(t[1], t[2])
        }
        ,
        d.result = function(t, e, i) {
            var n = -1
              , r = (e = ce(e, t) ? [e] : bt(e)).length;
            for (r || (t = xs,
            r = 1); ++n < r; ) {
                var o = null == t ? xs : t[we(e[n])];
                o === xs && (n = r,
                o = i),
                t = Xe(o) ? o.call(t) : o
            }
            return t
        }
        ,
        d.round = Io,
        d.runInContext = t,
        d.sample = function(t) {
            return (Lr(t) ? n : function(t) {
                return n(pi(t))
            }
            )(t)
        }
        ,
        d.size = function(t) {
            if (null == t)
                return 0;
            if (He(t))
                return ti(t) ? ws(t) : t.length;
            var e = _(t);
            return "[object Map]" == e || "[object Set]" == e ? t.size : V(t).length
        }
        ,
        d.snakeCase = vo,
        d.some = function(t, e, i) {
            var n = Lr(t) ? Jo : ut;
            return i && ue(t, e, i) && (e = xs),
            n(t, ee(e, 3))
        }
        ,
        d.sortedIndex = function(t, e) {
            return ct(t, e)
        }
        ,
        d.sortedIndexBy = function(t, e, i) {
            return ht(t, e, ee(i, 2))
        }
        ,
        d.sortedIndexOf = function(t, e) {
            var i = t ? t.length : 0;
            if (i) {
                var n = ct(t, e);
                if (n < i && We(t[n], e))
                    return n
            }
            return -1
        }
        ,
        d.sortedLastIndex = function(t, e) {
            return ct(t, e, !0)
        }
        ,
        d.sortedLastIndexBy = function(t, e, i) {
            return ht(t, e, ee(i, 2), !0)
        }
        ,
        d.sortedLastIndexOf = function(t, e) {
            if (t && t.length) {
                var i = ct(t, e, !0) - 1;
                if (We(t[i], e))
                    return i
            }
            return -1
        }
        ,
        d.startCase = yo,
        d.startsWith = function(t, e, i) {
            return t = li(t),
            i = x(ri(i), 0, t.length),
            e = pt(e),
            t.slice(i, i + e.length) == e
        }
        ,
        d.subtract = No,
        d.sum = function(t) {
            return t && t.length ? ls(t, yi) : 0
        }
        ,
        d.sumBy = function(t, e) {
            return t && t.length ? ls(t, ee(e, 2)) : 0
        }
        ,
        d.template = function(s, t, e) {
            var i = d.templateSettings;
            e && ue(s, t, e) && (t = xs),
            s = li(s),
            t = Jr({}, t, i, h);
            var a, l, n = hi(e = Jr({}, t.imports, i.imports, h)), r = hs(e, n), u = 0;
            e = t.interpolate || na;
            var c = "__p+='";
            e = Ri((t.escape || na).source + "|" + e.source + "|" + (e === Ms ? Ys : na).source + "|" + (t.evaluate || na).source + "|$", "g");
            var o = "sourceURL"in t ? "//# sourceURL=" + t.sourceURL + "\n" : "";
            if (s.replace(e, function(t, e, i, n, r, o) {
                return i || (i = n),
                c += s.slice(u, o).replace(ra, gs),
                e && (a = !0,
                c += "'+__e(" + e + ")+'"),
                r && (l = !0,
                c += "';" + r + ";\n__p+='"),
                i && (c += "'+((__t=(" + i + "))==null?'':__t)+'"),
                u = o + t.length,
                t
            }),
            c += "';",
            (t = t.variable) || (c = "with(obj){" + c + "}"),
            c = (l ? c.replace(ks, "") : c).replace(Ss, "$1").replace($s, "$1;"),
            c = "function(" + (t || "obj") + "){" + (t ? "" : "obj||(obj={});") + "var __t,__p=''" + (a ? ",__e=_.escape" : "") + (l ? ",__j=Array.prototype.join;function print(){__p+=__j.call(arguments,'')}" : ";") + c + "return __p}",
            (t = xo(function() {
                return Ei(n, o + "return " + c).apply(xs, r)
            })).source = c,
            Ve(t))
                throw t;
            return t
        }
        ,
        d.times = function(t, e) {
            if ((t = ri(t)) < 1 || 9007199254740991 < t)
                return [];
            var i = 4294967295
              , n = _n(t, 4294967295);
            for (t -= 4294967295,
            n = us(n, e = ee(e)); ++i < t; )
                e(i);
            return n
        }
        ,
        d.toFinite = ni,
        d.toInteger = ri,
        d.toLength = oi,
        d.toLower = function(t) {
            return li(t).toLowerCase()
        }
        ,
        d.toNumber = si,
        d.toSafeInteger = function(t) {
            return x(ri(t), -9007199254740991, 9007199254740991)
        }
        ,
        d.toString = li,
        d.toUpper = function(t) {
            return li(t).toUpperCase()
        }
        ,
        d.trim = function(t, e, i) {
            return (t = li(t)) && (i || e === xs) ? t.replace(Ls, "") : t && (e = pt(e)) ? xt(t = bs(t), e = ds(t, i = bs(e)), i = ps(t, i) + 1).join("") : t
        }
        ,
        d.trimEnd = function(t, e, i) {
            return (t = li(t)) && (i || e === xs) ? t.replace(Ws, "") : t && (e = pt(e)) ? xt(t = bs(t), 0, e = ps(t, bs(e)) + 1).join("") : t
        }
        ,
        d.trimStart = function(t, e, i) {
            return (t = li(t)) && (i || e === xs) ? t.replace(qs, "") : t && (e = pt(e)) ? xt(t = bs(t), e = ds(t, bs(e))).join("") : t
        }
        ,
        d.truncate = function(t, e) {
            var i = 30
              , n = "...";
            if (Ze(e)) {
                var r = "separator"in e ? e.separator : r;
                i = "length"in e ? ri(e.length) : i,
                n = "omission"in e ? pt(e.omission) : n
            }
            var o = (t = li(t)).length;
            if (la.test(t)) {
                var s = bs(t);
                o = s.length
            }
            if (o <= i)
                return t;
            if ((o = i - ws(n)) < 1)
                return n;
            if (i = s ? xt(s, 0, o).join("") : t.slice(0, o),
            r === xs)
                return i + n;
            if (s && (o += i.length - o),
            Vr(r)) {
                if (t.slice(o).search(r)) {
                    var a = i;
                    for (r.global || (r = Ri(r.source, li(Zs.exec(r)) + "g")),
                    r.lastIndex = 0; s = r.exec(a); )
                        var l = s.index;
                    i = i.slice(0, l === xs ? o : l)
                }
            } else
                t.indexOf(pt(r), o) != o && (-1 < (r = i.lastIndexOf(r)) && (i = i.slice(0, r)));
            return i + n
        }
        ,
        d.unescape = function(t) {
            return (t = li(t)) && Es.test(t) ? t.replace(Ds, ka) : t
        }
        ,
        d.uniqueId = function(t) {
            var e = ++qi;
            return li(t) + e
        }
        ,
        d.upperCase = wo,
        d.upperFirst = bo,
        d.each = je,
        d.eachRight = ze,
        d.first = $e,
        bi(d, (Rr = {},
        P(d, function(t, e) {
            Li.call(d.prototype, e) || (Rr[e] = t)
        }),
        Rr), {
            chain: !1
        }),
        d.VERSION = "4.16.4",
        Ho("bind bindKey curry curryRight partial partialRight".split(" "), function(t) {
            d[t].placeholder = d
        }),
        Ho(["drop", "take"], function(n, r) {
            g.prototype[n] = function(t) {
                var e = this.__filtered__;
                if (e && !r)
                    return new g(this);
                t = t === xs ? 1 : gn(ri(t), 0);
                var i = this.clone();
                return e ? i.__takeCount__ = _n(t, i.__takeCount__) : i.__views__.push({
                    size: _n(t, 4294967295),
                    type: n + (i.__dir__ < 0 ? "Right" : "")
                }),
                i
            }
            ,
            g.prototype[n + "Right"] = function(t) {
                return this.reverse()[n](t).reverse()
            }
        }),
        Ho(["filter", "map", "takeWhile"], function(t, e) {
            var i = e + 1
              , n = 1 == i || 3 == i;
            g.prototype[t] = function(t) {
                var e = this.clone();
                return e.__iteratees__.push({
                    iteratee: ee(t, 3),
                    type: i
                }),
                e.__filtered__ = e.__filtered__ || n,
                e
            }
        }),
        Ho(["head", "last"], function(t, e) {
            var i = "take" + (e ? "Right" : "");
            g.prototype[t] = function() {
                return this[i](1).value()[0]
            }
        }),
        Ho(["initial", "tail"], function(t, e) {
            var i = "drop" + (e ? "" : "Right");
            g.prototype[t] = function() {
                return this.__filtered__ ? new g(this) : this[i](1)
            }
        }),
        g.prototype.compact = function() {
            return this.filter(yi)
        }
        ,
        g.prototype.find = function(t) {
            return this.filter(t).head()
        }
        ,
        g.prototype.findLast = function(t) {
            return this.reverse().find(t)
        }
        ,
        g.prototype.invokeMap = st(function(e, i) {
            return "function" == typeof e ? new g(this) : this.map(function(t) {
                return B(t, e, i)
            })
        }),
        g.prototype.reject = function(t) {
            return this.filter(qe(ee(t)))
        }
        ,
        g.prototype.slice = function(t, e) {
            t = ri(t);
            var i = this;
            return i.__filtered__ && (0 < t || e < 0) ? new g(i) : (t < 0 ? i = i.takeRight(-t) : t && (i = i.drop(t)),
            e !== xs && (i = (e = ri(e)) < 0 ? i.dropRight(-e) : i.take(e - t)),
            i)
        }
        ,
        g.prototype.takeRightWhile = function(t) {
            return this.reverse().takeWhile(t).reverse()
        }
        ,
        g.prototype.toArray = function() {
            return this.take(4294967295)
        }
        ,
        P(g.prototype, function(l, t) {
            var u = /^(?:filter|find|map|reject)|While$/.test(t)
              , c = /^(?:head|last)$/.test(t)
              , h = d[c ? "take" + ("last" == t ? "Right" : "") : t]
              , f = c || /^find/.test(t);
            h && (d.prototype[t] = function() {
                function t(t) {
                    return t = h.apply(d, Zo([t], i)),
                    c && s ? t[0] : t
                }
                var e = this.__wrapped__
                  , i = c ? [1] : arguments
                  , n = e instanceof g
                  , r = i[0]
                  , o = n || Lr(e);
                o && u && "function" == typeof r && 1 != r.length && (n = o = !1);
                var s = this.__chain__
                  , a = !!this.__actions__.length;
                r = f && !s,
                n = n && !a;
                return !f && o ? (e = n ? e : new g(this),
                (e = l.apply(e, i)).__actions__.push({
                    func: Me,
                    args: [t],
                    thisArg: xs
                }),
                new p(e,s)) : r && n ? l.apply(this, i) : (e = this.thru(t),
                r ? c ? e.value()[0] : e.value() : e)
            }
            )
        }),
        Ho("pop push shift sort splice unshift".split(" "), function(t) {
            var i = zi[t]
              , n = /^(?:push|sort|unshift)$/.test(t) ? "tap" : "thru"
              , r = /^(?:pop|shift)$/.test(t);
            d.prototype[t] = function() {
                var e = arguments;
                if (!r || this.__chain__)
                    return this[n](function(t) {
                        return i.apply(Lr(t) ? t : [], e)
                    });
                var t = this.value();
                return i.apply(Lr(t) ? t : [], e)
            }
        }),
        P(g.prototype, function(t, e) {
            var i = d[e];
            if (i) {
                var n = i.name + "";
                (Dn[n] || (Dn[n] = [])).push({
                    name: e,
                    func: i
                })
            }
        }),
        Dn[Bt(xs, 2).name] = [{
            name: "wrapper",
            func: xs
        }],
        g.prototype.clone = function() {
            var t = new g(this.__wrapped__);
            return t.__actions__ = At(this.__actions__),
            t.__dir__ = this.__dir__,
            t.__filtered__ = this.__filtered__,
            t.__iteratees__ = At(this.__iteratees__),
            t.__takeCount__ = this.__takeCount__,
            t.__views__ = At(this.__views__),
            t
        }
        ,
        g.prototype.reverse = function() {
            if (this.__filtered__) {
                var t = new g(this);
                t.__dir__ = -1,
                t.__filtered__ = !0
            } else
                (t = this.clone()).__dir__ *= -1;
            return t
        }
        ,
        g.prototype.value = function() {
            var t, e = this.__wrapped__.value(), i = this.__dir__, n = Lr(e), r = i < 0, o = n ? e.length : 0;
            t = o;
            for (var s = this.__views__, a = 0, l = -1, u = s.length; ++l < u; ) {
                var c = s[l]
                  , h = c.size;
                switch (c.type) {
                case "drop":
                    a += h;
                    break;
                case "dropRight":
                    t -= h;
                    break;
                case "take":
                    t = _n(t, a + h);
                    break;
                case "takeRight":
                    a = gn(a, t - h)
                }
            }
            if (s = (t = {
                start: a,
                end: t
            }).start,
            t = (a = t.end) - s,
            r = r ? a : s - 1,
            a = (s = this.__iteratees__).length,
            l = 0,
            u = _n(t, this.__takeCount__),
            !n || o < 200 || o == t && u == t)
                return mt(e, this.__actions__);
            n = [];
            t: for (; t-- && l < u; ) {
                for (o = -1,
                c = e[r += i]; ++o < a; ) {
                    h = (f = s[o]).type;
                    var f = (0,
                    f.iteratee)(c);
                    if (2 == h)
                        c = f;
                    else if (!f) {
                        if (1 == h)
                            continue t;
                        break t
                    }
                }
                n[l++] = c
            }
            return n
        }
        ,
        d.prototype.at = yr,
        d.prototype.chain = function() {
            return Re(this)
        }
        ,
        d.prototype.commit = function() {
            return new p(this.value(),this.__chain__)
        }
        ,
        d.prototype.next = function() {
            this.__values__ === xs && (this.__values__ = ii(this.value()));
            var t = this.__index__ >= this.__values__.length;
            return {
                done: t,
                value: t ? xs : this.__values__[this.__index__++]
            }
        }
        ,
        d.prototype.plant = function(t) {
            for (var e, i = this; i instanceof o; ) {
                var n = Te(i);
                n.__index__ = 0,
                n.__values__ = xs,
                e ? r.__wrapped__ = n : e = n;
                var r = n;
                i = i.__wrapped__
            }
            return r.__wrapped__ = t,
            e
        }
        ,
        d.prototype.reverse = function() {
            var t = this.__wrapped__;
            return t instanceof g ? (this.__actions__.length && (t = new g(this)),
            (t = t.reverse()).__actions__.push({
                func: Me,
                args: [Ee],
                thisArg: xs
            }),
            new p(t,this.__chain__)) : this.thru(Ee)
        }
        ,
        d.prototype.toJSON = d.prototype.valueOf = d.prototype.value = function() {
            return mt(this.__wrapped__, this.__actions__)
        }
        ,
        d.prototype.first = d.prototype.head,
        Ki && (d.prototype[Ki] = function() {
            return this
        }
        ),
        d
    }();
    "function" == typeof define && "object" == typeof define.amd && define.amd ? (ga._ = Sa,
    define(function() {
        return Sa
    })) : u ? ((u.exports = Sa)._ = Sa,
    l._ = Sa) : ga._ = Sa
}
.call(this);
var _gsScope, _slice = Array.prototype.slice, _slicedToArray = function(t, e) {
    if (Array.isArray(t))
        return t;
    if (Symbol.iterator in Object(t))
        return function(t, e) {
            var i = []
              , n = !0
              , r = !1
              , o = void 0;
            try {
                for (var s, a = t[Symbol.iterator](); !(n = (s = a.next()).done) && (i.push(s.value),
                !e || i.length !== e); n = !0)
                    ;
            } catch (t) {
                r = !0,
                o = t
            } finally {
                try {
                    !n && a.return && a.return()
                } finally {
                    if (r)
                        throw o
                }
            }
            return i
        }(t, e);
    throw new TypeError("Invalid attempt to destructure non-iterable instance")
}, _extends = Object.assign || function(t) {
    for (var e = 1; e < arguments.length; e++) {
        var i = arguments[e];
        for (var n in i)
            Object.prototype.hasOwnProperty.call(i, n) && (t[n] = i[n])
    }
    return t
}
;
!function(t, e) {
    "object" == typeof exports && "undefined" != typeof module ? module.exports = e(require("jquery")) : "function" == typeof define && define.amd ? define(["jquery"], e) : t.parsley = e(t.jQuery)
}(this, function(h) {
    "use strict";
    function n(e, i) {
        return e.parsleyAdaptedCallback || (e.parsleyAdaptedCallback = function() {
            var t = Array.prototype.slice.call(arguments, 0);
            t.unshift(this),
            e.apply(i || $, t)
        }
        ),
        e.parsleyAdaptedCallback
    }
    function o(t) {
        return 0 === t.lastIndexOf("parsley:", 0) ? t.substr("parsley:".length) : t
    }
    var i, t = 1, e = {}, l = {
        attr: function(t, e, i) {
            var n, r, o, s = new RegExp("^" + e,"i");
            if (void 0 === i)
                i = {};
            else
                for (n in i)
                    i.hasOwnProperty(n) && delete i[n];
            if (!t)
                return i;
            for (n = (o = t.attributes).length; n--; )
                (r = o[n]) && r.specified && s.test(r.name) && (i[this.camelize(r.name.slice(e.length))] = this.deserializeValue(r.value));
            return i
        },
        checkAttr: function(t, e, i) {
            return t.hasAttribute(e + i)
        },
        setAttr: function(t, e, i, n) {
            t.setAttribute(this.dasherize(e + i), String(n))
        },
        getType: function(t) {
            return t.getAttribute("type") || "text"
        },
        generateID: function() {
            return "" + t++
        },
        deserializeValue: function(e) {
            var t;
            try {
                return e ? "true" == e || "false" != e && ("null" == e ? null : isNaN(t = Number(e)) ? /^[\[\{]/.test(e) ? JSON.parse(e) : e : t) : e
            } catch (t) {
                return e
            }
        },
        camelize: function(t) {
            return t.replace(/-+(.)?/g, function(t, e) {
                return e ? e.toUpperCase() : ""
            })
        },
        dasherize: function(t) {
            return t.replace(/::/g, "/").replace(/([A-Z]+)([A-Z][a-z])/g, "$1_$2").replace(/([a-z\d])([A-Z])/g, "$1_$2").replace(/_/g, "-").toLowerCase()
        },
        warn: function() {
            var t;
            window.console && "function" == typeof window.console.warn && (t = window.console).warn.apply(t, arguments)
        },
        warnOnce: function(t) {
            e[t] || (e[t] = !0,
            this.warn.apply(this, arguments))
        },
        _resetWarnings: function() {
            e = {}
        },
        trimString: function(t) {
            return t.replace(/^\s+|\s+$/g, "")
        },
        parse: {
            date: function(t) {
                var e = t.match(/^(\d{4,})-(\d\d)-(\d\d)$/);
                if (!e)
                    return null;
                var i = e.map(function(t) {
                    return parseInt(t, 10)
                })
                  , n = _slicedToArray(i, 4)
                  , r = (n[0],
                n[1])
                  , o = n[2]
                  , s = n[3]
                  , a = new Date(r,o - 1,s);
                return a.getFullYear() !== r || a.getMonth() + 1 !== o || a.getDate() !== s ? null : a
            },
            string: function(t) {
                return t
            },
            integer: function(t) {
                return isNaN(t) ? null : parseInt(t, 10)
            },
            number: function(t) {
                if (isNaN(t))
                    throw null;
                return parseFloat(t)
            },
            boolean: function(t) {
                return !/^\s*false\s*$/i.test(t)
            },
            object: function(t) {
                return l.deserializeValue(t)
            },
            regexp: function(t) {
                var e = "";
                return t = /^\/.*\/(?:[gimy]*)$/.test(t) ? (e = t.replace(/.*\/([gimy]*)$/, "$1"),
                t.replace(new RegExp("^/(.*?)/" + e + "$"), "$1")) : "^" + t + "$",
                new RegExp(t,e)
            }
        },
        parseRequirement: function(t, e) {
            var i = this.parse[t || "string"];
            if (!i)
                throw 'Unknown requirement specification: "' + t + '"';
            var n = i(e);
            if (null === n)
                throw "Requirement is not a " + t + ': "' + e + '"';
            return n
        },
        namespaceEvents: function(t, e) {
            return (t = this.trimString(t || "").split(/\s+/))[0] ? h.map(t, function(t) {
                return t + "." + e
            }).join(" ") : ""
        },
        difference: function(t, i) {
            var n = [];
            return h.each(t, function(t, e) {
                -1 == i.indexOf(e) && n.push(e)
            }),
            n
        },
        all: function(t) {
            return h.when.apply(h, _toConsumableArray(t).concat([42, 42]))
        },
        objectCreate: Object.create || (i = function() {}
        ,
        function(t) {
            if (1 < arguments.length)
                throw Error("Second argument not supported");
            if ("object" != typeof t)
                throw TypeError("Argument must be an object");
            i.prototype = t;
            var e = new i;
            return i.prototype = null,
            e
        }
        ),
        _SubmitSelector: 'input[type="submit"], button:submit'
    }, r = {
        namespace: "data-parsley-",
        inputs: "input, textarea, select",
        excluded: "input[type=button], input[type=submit], input[type=reset], input[type=hidden]",
        priorityEnabled: !0,
        multiple: null,
        group: null,
        uiEnabled: !0,
        validationThreshold: 3,
        focus: "first",
        trigger: !1,
        triggerAfterFailure: "input",
        errorClass: "parsley-error",
        successClass: "parsley-success",
        classHandler: function(t) {},
        errorsContainer: function(t) {},
        errorsWrapper: '<ul class="parsley-errors-list"></ul>',
        errorTemplate: "<li></li>"
    }, s = function() {
        this.__id__ = l.generateID()
    };
    s.prototype = {
        asyncSupport: !0,
        _pipeAccordingToValidationResult: function() {
            var e = this
              , t = function() {
                var t = h.Deferred();
                return !0 !== e.validationResult && t.reject(),
                t.resolve().promise()
            };
            return [t, t]
        },
        actualizeOptions: function() {
            return l.attr(this.element, this.options.namespace, this.domOptions),
            this.parent && this.parent.actualizeOptions && this.parent.actualizeOptions(),
            this
        },
        _resetOptions: function(t) {
            for (var e in this.domOptions = l.objectCreate(this.parent.options),
            this.options = l.objectCreate(this.domOptions),
            t)
                t.hasOwnProperty(e) && (this.options[e] = t[e]);
            this.actualizeOptions()
        },
        _listeners: null,
        on: function(t, e) {
            return this._listeners = this._listeners || {},
            (this._listeners[t] = this._listeners[t] || []).push(e),
            this
        },
        subscribe: function(t, e) {
            h.listenTo(this, t.toLowerCase(), e)
        },
        off: function(t, e) {
            var i = this._listeners && this._listeners[t];
            if (i)
                if (e)
                    for (var n = i.length; n--; )
                        i[n] === e && i.splice(n, 1);
                else
                    delete this._listeners[t];
            return this
        },
        unsubscribe: function(t, e) {
            h.unsubscribeTo(this, t.toLowerCase())
        },
        trigger: function(t, e, i) {
            e = e || this;
            var n, r = this._listeners && this._listeners[t];
            if (r)
                for (var o = r.length; o--; )
                    if (!1 === (n = r[o].call(e, e, i)))
                        return n;
            return !this.parent || this.parent.trigger(t, e, i)
        },
        asyncIsValid: function(t, e) {
            return l.warnOnce("asyncIsValid is deprecated; please use whenValid instead"),
            this.whenValid({
                group: t,
                force: e
            })
        },
        _findRelated: function() {
            return this.options.multiple ? h(this.parent.element.querySelectorAll("[" + this.options.namespace + 'multiple="' + this.options.multiple + '"]')) : this.$element
        }
    };
    var a = function(t) {
        h.extend(!0, this, t)
    };
    a.prototype = {
        validate: function(t, e) {
            if (this.fn)
                return 3 < arguments.length && (e = [].slice.call(arguments, 1, -1)),
                this.fn(t, e);
            if (Array.isArray(t)) {
                if (!this.validateMultiple)
                    throw "Validator `" + this.name + "` does not handle multiple values";
                return this.validateMultiple.apply(this, arguments)
            }
            var i = arguments[arguments.length - 1];
            if (this.validateDate && i._isDateInput())
                return null !== (t = l.parse.date(t)) && this.validateDate.apply(this, arguments);
            if (this.validateNumber)
                return !isNaN(t) && (t = parseFloat(t),
                this.validateNumber.apply(this, arguments));
            if (this.validateString)
                return this.validateString.apply(this, arguments);
            throw "Validator `" + this.name + "` only handles multiple values"
        },
        parseRequirements: function(t, e) {
            if ("string" != typeof t)
                return Array.isArray(t) ? t : [t];
            var i = this.requirementType;
            if (Array.isArray(i)) {
                for (var n = function(t, e) {
                    var i = t.match(/^\s*\[(.*)\]\s*$/);
                    if (!i)
                        throw 'Requirement is not an array: "' + t + '"';
                    var n = i[1].split(",").map(l.trimString);
                    if (n.length !== e)
                        throw "Requirement has " + n.length + " values when " + e + " are needed";
                    return n
                }(t, i.length), r = 0; r < n.length; r++)
                    n[r] = l.parseRequirement(i[r], n[r]);
                return n
            }
            return h.isPlainObject(i) ? function(t, e, i) {
                var n = null
                  , r = {};
                for (var o in t)
                    if (o) {
                        var s = i(o);
                        "string" == typeof s && (s = l.parseRequirement(t[o], s)),
                        r[o] = s
                    } else
                        n = l.parseRequirement(t[o], e);
                return [n, r]
            }(i, t, e) : [l.parseRequirement(i, t)]
        },
        requirementType: "string",
        priority: 2
    };
    var u = function(t, e) {
        this.__class__ = "ValidatorRegistry",
        this.locale = "en",
        this.init(t || {}, e || {})
    }
      , f = {
        email: /^((([a-zA-Z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-zA-Z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-zA-Z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-zA-Z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-zA-Z]|\d|-|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-zA-Z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-zA-Z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-zA-Z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-zA-Z]|\d|-|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-zA-Z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/,
        number: /^-?(\d*\.)?\d+(e[-+]?\d+)?$/i,
        integer: /^-?\d+$/,
        digits: /^\d+$/,
        alphanum: /^\w+$/i,
        date: {
            test: function(t) {
                return null !== l.parse.date(t)
            }
        },
        url: new RegExp("^(?:(?:https?|ftp)://)?(?:\\S+(?::\\S*)?@)?(?:(?:[1-9]\\d?|1\\d\\d|2[01]\\d|22[0-3])(?:\\.(?:1?\\d{1,2}|2[0-4]\\d|25[0-5])){2}(?:\\.(?:[1-9]\\d?|1\\d\\d|2[0-4]\\d|25[0-4]))|(?:(?:[a-zA-Z\\u00a1-\\uffff0-9]-*)*[a-zA-Z\\u00a1-\\uffff0-9]+)(?:\\.(?:[a-zA-Z\\u00a1-\\uffff0-9]-*)*[a-zA-Z\\u00a1-\\uffff0-9]+)*(?:\\.(?:[a-zA-Z\\u00a1-\\uffff]{2,})))(?::\\d{2,5})?(?:/\\S*)?$")
    };
    f.range = f.number;
    var d = function(t) {
        var e = ("" + t).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
        return e ? Math.max(0, (e[1] ? e[1].length : 0) - (e[2] ? +e[2] : 0)) : 0
    }
      , c = function(o, s) {
        return function(t) {
            for (var e = arguments.length, i = Array(1 < e ? e - 1 : 0), n = 1; n < e; n++)
                i[n - 1] = arguments[n];
            return i.pop(),
            s.apply(void 0, [t].concat(_toConsumableArray((r = o,
            i.map(l.parse[r])))));
            var r
        }
    }
      , p = function(t) {
        return {
            validateDate: c("date", t),
            validateNumber: c("number", t),
            requirementType: t.length <= 2 ? "string" : ["string", "string"],
            priority: 30
        }
    };
    u.prototype = {
        init: function(t, e) {
            for (var i in this.catalog = e,
            this.validators = _extends({}, this.validators),
            t)
                this.addValidator(i, t[i].fn, t[i].priority);
            window.Parsley.trigger("parsley:validator:init")
        },
        setLocale: function(t) {
            if (void 0 === this.catalog[t])
                throw new Error(t + " is not available in the catalog");
            return this.locale = t,
            this
        },
        addCatalog: function(t, e, i) {
            return "object" == typeof e && (this.catalog[t] = e),
            !0 === i ? this.setLocale(t) : this
        },
        addMessage: function(t, e, i) {
            return void 0 === this.catalog[t] && (this.catalog[t] = {}),
            this.catalog[t][e] = i,
            this
        },
        addMessages: function(t, e) {
            for (var i in e)
                this.addMessage(t, i, e[i]);
            return this
        },
        addValidator: function(t, e, i) {
            if (this.validators[t])
                l.warn('Validator "' + t + '" is already defined.');
            else if (r.hasOwnProperty(t))
                return void l.warn('"' + t + '" is a restricted keyword and is not a valid validator name.');
            return this._setValidator.apply(this, arguments)
        },
        hasValidator: function(t) {
            return !!this.validators[t]
        },
        updateValidator: function(t, e, i) {
            return this.validators[t] ? this._setValidator.apply(this, arguments) : (l.warn('Validator "' + t + '" is not already defined.'),
            this.addValidator.apply(this, arguments))
        },
        removeValidator: function(t) {
            return this.validators[t] || l.warn('Validator "' + t + '" is not defined.'),
            delete this.validators[t],
            this
        },
        _setValidator: function(t, e, i) {
            for (var n in "object" != typeof e && (e = {
                fn: e,
                priority: i
            }),
            e.validate || (e = new a(e)),
            (this.validators[t] = e).messages || {})
                this.addMessage(n, t, e.messages[n]);
            return this
        },
        getErrorMessage: function(t) {
            var e;
            "type" === t.name ? e = (this.catalog[this.locale][t.name] || {})[t.requirements] : e = this.formatMessage(this.catalog[this.locale][t.name], t.requirements);
            return e || this.catalog[this.locale].defaultMessage || this.catalog.en.defaultMessage
        },
        formatMessage: function(t, e) {
            if ("object" != typeof e)
                return "string" == typeof t ? t.replace(/%s/i, e) : "";
            for (var i in e)
                t = this.formatMessage(t, e[i]);
            return t
        },
        validators: {
            notblank: {
                validateString: function(t) {
                    return /\S/.test(t)
                },
                priority: 2
            },
            required: {
                validateMultiple: function(t) {
                    return 0 < t.length
                },
                validateString: function(t) {
                    return /\S/.test(t)
                },
                priority: 512
            },
            type: {
                validateString: function(t, e) {
                    var i = arguments.length <= 2 || void 0 === arguments[2] ? {} : arguments[2]
                      , n = i.step
                      , r = void 0 === n ? "any" : n
                      , o = i.base
                      , s = void 0 === o ? 0 : o
                      , a = f[e];
                    if (!a)
                        throw new Error("validator type `" + e + "` is not supported");
                    if (!a.test(t))
                        return !1;
                    if ("number" === e && !/^any$/i.test(r || "")) {
                        var l = Number(t)
                          , u = Math.max(d(r), d(s));
                        if (d(l) > u)
                            return !1;
                        var c = function(t) {
                            return Math.round(t * Math.pow(10, u))
                        };
                        if ((c(l) - c(s)) % c(r) != 0)
                            return !1
                    }
                    return !0
                },
                requirementType: {
                    "": "string",
                    step: "string",
                    base: "number"
                },
                priority: 256
            },
            pattern: {
                validateString: function(t, e) {
                    return e.test(t)
                },
                requirementType: "regexp",
                priority: 64
            },
            minlength: {
                validateString: function(t, e) {
                    return t.length >= e
                },
                requirementType: "integer",
                priority: 30
            },
            maxlength: {
                validateString: function(t, e) {
                    return t.length <= e
                },
                requirementType: "integer",
                priority: 30
            },
            length: {
                validateString: function(t, e, i) {
                    return t.length >= e && t.length <= i
                },
                requirementType: ["integer", "integer"],
                priority: 30
            },
            mincheck: {
                validateMultiple: function(t, e) {
                    return t.length >= e
                },
                requirementType: "integer",
                priority: 30
            },
            maxcheck: {
                validateMultiple: function(t, e) {
                    return t.length <= e
                },
                requirementType: "integer",
                priority: 30
            },
            check: {
                validateMultiple: function(t, e, i) {
                    return t.length >= e && t.length <= i
                },
                requirementType: ["integer", "integer"],
                priority: 30
            },
            min: p(function(t, e) {
                return e <= t
            }),
            max: p(function(t, e) {
                return t <= e
            }),
            range: p(function(t, e, i) {
                return e <= t && t <= i
            }),
            equalto: {
                validateString: function(t, e) {
                    var i = h(e);
                    return i.length ? t === i.val() : t === e
                },
                priority: 256
            }
        }
    };
    var g = {};
    g.Form = {
        _actualizeTriggers: function() {
            var e = this;
            this.$element.on("submit.Parsley", function(t) {
                e.onSubmitValidate(t)
            }),
            this.$element.on("click.Parsley", l._SubmitSelector, function(t) {
                e.onSubmitButton(t)
            }),
            !1 !== this.options.uiEnabled && this.element.setAttribute("novalidate", "")
        },
        focus: function() {
            if (!(this._focusedField = null) === this.validationResult || "none" === this.options.focus)
                return null;
            for (var t = 0; t < this.fields.length; t++) {
                var e = this.fields[t];
                if (!0 !== e.validationResult && 0 < e.validationResult.length && void 0 === e.options.noFocus && (this._focusedField = e.$element,
                "first" === this.options.focus))
                    break
            }
            return null === this._focusedField ? null : this._focusedField.focus()
        },
        _destroyUI: function() {
            this.$element.off(".Parsley")
        }
    },
    g.Field = {
        _reflowUI: function() {
            if (this._buildUI(),
            this._ui) {
                var t = function t(e, i, n) {
                    for (var r = [], o = [], s = 0; s < e.length; s++) {
                        for (var a = !1, l = 0; l < i.length; l++)
                            if (e[s].assert.name === i[l].assert.name) {
                                a = !0;
                                break
                            }
                        a ? o.push(e[s]) : r.push(e[s])
                    }
                    return {
                        kept: o,
                        added: r,
                        removed: n ? [] : t(i, e, !0).added
                    }
                }(this.validationResult, this._ui.lastValidationResult);
                this._ui.lastValidationResult = this.validationResult,
                this._manageStatusClass(),
                this._manageErrorsMessages(t),
                this._actualizeTriggers(),
                !t.kept.length && !t.added.length || this._failedOnce || (this._failedOnce = !0,
                this._actualizeTriggers())
            }
        },
        getErrorsMessages: function() {
            if (!0 === this.validationResult)
                return [];
            for (var t = [], e = 0; e < this.validationResult.length; e++)
                t.push(this.validationResult[e].errorMessage || this._getErrorMessage(this.validationResult[e].assert));
            return t
        },
        addError: function(t) {
            var e = arguments.length <= 1 || void 0 === arguments[1] ? {} : arguments[1]
              , i = e.message
              , n = e.assert
              , r = e.updateClass
              , o = void 0 === r || r;
            this._buildUI(),
            this._addError(t, {
                message: i,
                assert: n
            }),
            o && this._errorClass()
        },
        updateError: function(t) {
            var e = arguments.length <= 1 || void 0 === arguments[1] ? {} : arguments[1]
              , i = e.message
              , n = e.assert
              , r = e.updateClass
              , o = void 0 === r || r;
            this._buildUI(),
            this._updateError(t, {
                message: i,
                assert: n
            }),
            o && this._errorClass()
        },
        removeError: function(t) {
            var e = (arguments.length <= 1 || void 0 === arguments[1] ? {} : arguments[1]).updateClass
              , i = void 0 === e || e;
            this._buildUI(),
            this._removeError(t),
            i && this._manageStatusClass()
        },
        _manageStatusClass: function() {
            this.hasConstraints() && this.needsValidation() && !0 === this.validationResult ? this._successClass() : 0 < this.validationResult.length ? this._errorClass() : this._resetClass()
        },
        _manageErrorsMessages: function(t) {
            if (void 0 === this.options.errorsMessagesDisabled) {
                if (void 0 !== this.options.errorMessage)
                    return t.added.length || t.kept.length ? (this._insertErrorWrapper(),
                    0 === this._ui.$errorsWrapper.find(".parsley-custom-error-message").length && this._ui.$errorsWrapper.append(h(this.options.errorTemplate).addClass("parsley-custom-error-message")),
                    this._ui.$errorsWrapper.addClass("filled").find(".parsley-custom-error-message").html(this.options.errorMessage)) : this._ui.$errorsWrapper.removeClass("filled").find(".parsley-custom-error-message").remove();
                for (var e = 0; e < t.removed.length; e++)
                    this._removeError(t.removed[e].assert.name);
                for (e = 0; e < t.added.length; e++)
                    this._addError(t.added[e].assert.name, {
                        message: t.added[e].errorMessage,
                        assert: t.added[e].assert
                    });
                for (e = 0; e < t.kept.length; e++)
                    this._updateError(t.kept[e].assert.name, {
                        message: t.kept[e].errorMessage,
                        assert: t.kept[e].assert
                    })
            }
        },
        _addError: function(t, e) {
            var i = e.message
              , n = e.assert;
            this._insertErrorWrapper(),
            this._ui.$errorClassHandler.attr("aria-describedby", this._ui.errorsWrapperId),
            this._ui.$errorsWrapper.addClass("filled").append(h(this.options.errorTemplate).addClass("parsley-" + t).html(i || this._getErrorMessage(n)))
        },
        _updateError: function(t, e) {
            var i = e.message
              , n = e.assert;
            this._ui.$errorsWrapper.addClass("filled").find(".parsley-" + t).html(i || this._getErrorMessage(n))
        },
        _removeError: function(t) {
            this._ui.$errorClassHandler.removeAttr("aria-describedby"),
            this._ui.$errorsWrapper.removeClass("filled").find(".parsley-" + t).remove()
        },
        _getErrorMessage: function(t) {
            var e = t.name + "Message";
            return void 0 !== this.options[e] ? window.Parsley.formatMessage(this.options[e], t.requirements) : window.Parsley.getErrorMessage(t)
        },
        _buildUI: function() {
            if (!this._ui && !1 !== this.options.uiEnabled) {
                var t = {};
                this.element.setAttribute(this.options.namespace + "id", this.__id__),
                t.$errorClassHandler = this._manageClassHandler(),
                t.errorsWrapperId = "parsley-id-" + (this.options.multiple ? "multiple-" + this.options.multiple : this.__id__),
                t.$errorsWrapper = h(this.options.errorsWrapper).attr("id", t.errorsWrapperId),
                t.lastValidationResult = [],
                t.validationInformationVisible = !1,
                this._ui = t
            }
        },
        _manageClassHandler: function() {
            if ("string" == typeof this.options.classHandler && h(this.options.classHandler).length)
                return h(this.options.classHandler);
            var t = this.options.classHandler;
            if ("string" == typeof this.options.classHandler && "function" == typeof window[this.options.classHandler] && (t = window[this.options.classHandler]),
            "function" == typeof t) {
                var e = t.call(this, this);
                if (void 0 !== e && e.length)
                    return e
            } else {
                if ("object" == typeof t && t instanceof jQuery && t.length)
                    return t;
                t && l.warn("The class handler `" + t + "` does not exist in DOM nor as a global JS function")
            }
            return this._inputHolder()
        },
        _inputHolder: function() {
            return this.options.multiple && "SELECT" !== this.element.nodeName ? this.$element.parent() : this.$element
        },
        _insertErrorWrapper: function() {
            var t = this.options.errorsContainer;
            if (0 !== this._ui.$errorsWrapper.parent().length)
                return this._ui.$errorsWrapper.parent();
            if ("string" == typeof t) {
                if (h(t).length)
                    return h(t).append(this._ui.$errorsWrapper);
                "function" == typeof window[t] ? t = window[t] : l.warn("The errors container `" + t + "` does not exist in DOM nor as a global JS function")
            }
            return "function" == typeof t && (t = t.call(this, this)),
            "object" == typeof t && t.length ? t.append(this._ui.$errorsWrapper) : this._inputHolder().after(this._ui.$errorsWrapper)
        },
        _actualizeTriggers: function() {
            var t, e = this, i = this._findRelated();
            i.off(".Parsley"),
            this._failedOnce ? i.on(l.namespaceEvents(this.options.triggerAfterFailure, "Parsley"), function() {
                e._validateIfNeeded()
            }) : (t = l.namespaceEvents(this.options.trigger, "Parsley")) && i.on(t, function(t) {
                e._validateIfNeeded(t)
            })
        },
        _validateIfNeeded: function(t) {
            var e = this;
            t && /key|input/.test(t.type) && (!this._ui || !this._ui.validationInformationVisible) && this.getValue().length <= this.options.validationThreshold || (this.options.debounce ? (window.clearTimeout(this._debounced),
            this._debounced = window.setTimeout(function() {
                return e.validate()
            }, this.options.debounce)) : this.validate())
        },
        _resetUI: function() {
            this._failedOnce = !1,
            this._actualizeTriggers(),
            void 0 !== this._ui && (this._ui.$errorsWrapper.removeClass("filled").children().remove(),
            this._resetClass(),
            this._ui.lastValidationResult = [],
            this._ui.validationInformationVisible = !1)
        },
        _destroyUI: function() {
            this._resetUI(),
            void 0 !== this._ui && this._ui.$errorsWrapper.remove(),
            delete this._ui
        },
        _successClass: function() {
            this._ui.validationInformationVisible = !0,
            this._ui.$errorClassHandler.removeClass(this.options.errorClass).addClass(this.options.successClass)
        },
        _errorClass: function() {
            this._ui.validationInformationVisible = !0,
            this._ui.$errorClassHandler.removeClass(this.options.successClass).addClass(this.options.errorClass)
        },
        _resetClass: function() {
            this._ui.$errorClassHandler.removeClass(this.options.successClass).removeClass(this.options.errorClass)
        }
    };
    var _ = function(t, e, i) {
        this.__class__ = "Form",
        this.element = t,
        this.$element = h(t),
        this.domOptions = e,
        this.options = i,
        this.parent = window.Parsley,
        this.fields = [],
        this.validationResult = null
    }
      , m = {
        pending: null,
        resolved: !0,
        rejected: !1
    };
    _.prototype = {
        onSubmitValidate: function(t) {
            var e = this;
            if (!0 !== t.parsley) {
                var i = this._submitSource || this.$element.find(l._SubmitSelector)[0];
                if (this._submitSource = null,
                this.$element.find(".parsley-synthetic-submit-button").prop("disabled", !0),
                !i || null === i.getAttribute("formnovalidate")) {
                    window.Parsley._remoteCache = {};
                    var n = this.whenValidate({
                        event: t
                    });
                    "resolved" === n.state() && !1 !== this._trigger("submit") || (t.stopImmediatePropagation(),
                    t.preventDefault(),
                    "pending" === n.state() && n.done(function() {
                        e._submit(i)
                    }))
                }
            }
        },
        onSubmitButton: function(t) {
            this._submitSource = t.currentTarget
        },
        _submit: function(t) {
            if (!1 !== this._trigger("submit")) {
                if (t) {
                    var e = this.$element.find(".parsley-synthetic-submit-button").prop("disabled", !1);
                    0 === e.length && (e = h('<input class="parsley-synthetic-submit-button" type="hidden">').appendTo(this.$element)),
                    e.attr({
                        name: t.getAttribute("name"),
                        value: t.getAttribute("value")
                    })
                }
                this.$element.trigger(_extends(h.Event("submit"), {
                    parsley: !0
                }))
            }
        },
        validate: function(t) {
            if (1 <= arguments.length && !h.isPlainObject(t)) {
                l.warnOnce("Calling validate on a parsley form without passing arguments as an object is deprecated.");
                var e = _slice.call(arguments);
                t = {
                    group: e[0],
                    force: e[1],
                    event: e[2]
                }
            }
            return m[this.whenValidate(t).state()]
        },
        whenValidate: function() {
            var t, e = this, i = arguments.length <= 0 || void 0 === arguments[0] ? {} : arguments[0], n = i.group, r = i.force, o = i.event;
            (this.submitEvent = o) && (this.submitEvent = _extends({}, o, {
                preventDefault: function() {
                    l.warnOnce("Using `this.submitEvent.preventDefault()` is deprecated; instead, call `this.validationResult = false`"),
                    e.validationResult = !1
                }
            })),
            this.validationResult = !0,
            this._trigger("validate"),
            this._refreshFields();
            var s = this._withoutReactualizingFormOptions(function() {
                return h.map(e.fields, function(t) {
                    return t.whenValidate({
                        force: r,
                        group: n
                    })
                })
            });
            return (t = l.all(s).done(function() {
                e._trigger("success")
            }).fail(function() {
                e.validationResult = !1,
                e.focus(),
                e._trigger("error")
            }).always(function() {
                e._trigger("validated")
            })).pipe.apply(t, _toConsumableArray(this._pipeAccordingToValidationResult()))
        },
        isValid: function(t) {
            if (1 <= arguments.length && !h.isPlainObject(t)) {
                l.warnOnce("Calling isValid on a parsley form without passing arguments as an object is deprecated.");
                var e = _slice.call(arguments);
                t = {
                    group: e[0],
                    force: e[1]
                }
            }
            return m[this.whenValid(t).state()]
        },
        whenValid: function() {
            var t = this
              , e = arguments.length <= 0 || void 0 === arguments[0] ? {} : arguments[0]
              , i = e.group
              , n = e.force;
            this._refreshFields();
            var r = this._withoutReactualizingFormOptions(function() {
                return h.map(t.fields, function(t) {
                    return t.whenValid({
                        group: i,
                        force: n
                    })
                })
            });
            return l.all(r)
        },
        refresh: function() {
            return this._refreshFields(),
            this
        },
        reset: function() {
            for (var t = 0; t < this.fields.length; t++)
                this.fields[t].reset();
            this._trigger("reset")
        },
        destroy: function() {
            this._destroyUI();
            for (var t = 0; t < this.fields.length; t++)
                this.fields[t].destroy();
            this.$element.removeData("Parsley"),
            this._trigger("destroy")
        },
        _refreshFields: function() {
            return this.actualizeOptions()._bindFields()
        },
        _bindFields: function() {
            var r = this
              , t = this.fields;
            return this.fields = [],
            this.fieldsMappedById = {},
            this._withoutReactualizingFormOptions(function() {
                r.$element.find(r.options.inputs).not(r.options.excluded).each(function(t, e) {
                    var i = new window.Parsley.Factory(e,{},r);
                    if (("Field" === i.__class__ || "FieldMultiple" === i.__class__) && !0 !== i.options.excluded) {
                        var n = i.__class__ + "-" + i.__id__;
                        void 0 === r.fieldsMappedById[n] && (r.fieldsMappedById[n] = i,
                        r.fields.push(i))
                    }
                }),
                h.each(l.difference(t, r.fields), function(t, e) {
                    e.reset()
                })
            }),
            this
        },
        _withoutReactualizingFormOptions: function(t) {
            var e = this.actualizeOptions;
            this.actualizeOptions = function() {
                return this
            }
            ;
            var i = t();
            return this.actualizeOptions = e,
            i
        },
        _trigger: function(t) {
            return this.trigger("form:" + t)
        }
    };
    var v = function(t, e, i, n, r) {
        var o = window.Parsley._validatorRegistry.validators[e]
          , s = new a(o);
        n = n || t.options[e + "Priority"] || s.priority,
        _extends(this, {
            validator: s,
            name: e,
            requirements: i,
            priority: n,
            isDomConstraint: r = !0 === r
        }),
        this._parseRequirements(t.options)
    }
      , y = function(t, e, i, n) {
        this.__class__ = "Field",
        this.element = t,
        this.$element = h(t),
        void 0 !== n && (this.parent = n),
        this.options = i,
        this.domOptions = e,
        this.constraints = [],
        this.constraintsByName = {},
        this.validationResult = !0,
        this._bindConstraints()
    }
      , w = {
        pending: null,
        resolved: !0,
        rejected: !(v.prototype = {
            validate: function(t, e) {
                var i;
                return (i = this.validator).validate.apply(i, [t].concat(_toConsumableArray(this.requirementList), [e]))
            },
            _parseRequirements: function(i) {
                var n = this;
                this.requirementList = this.validator.parseRequirements(this.requirements, function(t) {
                    return i[n.name + (e = t,
                    e[0].toUpperCase() + e.slice(1))];
                    var e
                })
            }
        })
    };
    y.prototype = {
        validate: function(t) {
            1 <= arguments.length && !h.isPlainObject(t) && (l.warnOnce("Calling validate on a parsley field without passing arguments as an object is deprecated."),
            t = {
                options: t
            });
            var e = this.whenValidate(t);
            if (!e)
                return !0;
            switch (e.state()) {
            case "pending":
                return null;
            case "resolved":
                return !0;
            case "rejected":
                return this.validationResult
            }
        },
        whenValidate: function() {
            var t, e = this, i = arguments.length <= 0 || void 0 === arguments[0] ? {} : arguments[0], n = i.force, r = i.group;
            if (this.refresh(),
            !r || this._isInGroup(r))
                return this.value = this.getValue(),
                this._trigger("validate"),
                (t = this.whenValid({
                    force: n,
                    value: this.value,
                    _refreshed: !0
                }).always(function() {
                    e._reflowUI()
                }).done(function() {
                    e._trigger("success")
                }).fail(function() {
                    e._trigger("error")
                }).always(function() {
                    e._trigger("validated")
                })).pipe.apply(t, _toConsumableArray(this._pipeAccordingToValidationResult()))
        },
        hasConstraints: function() {
            return 0 !== this.constraints.length
        },
        needsValidation: function(t) {
            return void 0 === t && (t = this.getValue()),
            !(!t.length && !this._isRequired() && void 0 === this.options.validateIfEmpty)
        },
        _isInGroup: function(t) {
            return Array.isArray(this.options.group) ? -1 !== h.inArray(t, this.options.group) : this.options.group === t
        },
        isValid: function(t) {
            if (1 <= arguments.length && !h.isPlainObject(t)) {
                l.warnOnce("Calling isValid on a parsley field without passing arguments as an object is deprecated.");
                var e = _slice.call(arguments);
                t = {
                    force: e[0],
                    value: e[1]
                }
            }
            var i = this.whenValid(t);
            return !i || w[i.state()]
        },
        whenValid: function() {
            var n = this
              , t = arguments.length <= 0 || void 0 === arguments[0] ? {} : arguments[0]
              , e = t.force
              , i = void 0 !== e && e
              , r = t.value
              , o = t.group;
            if (t._refreshed || this.refresh(),
            !o || this._isInGroup(o)) {
                if (this.validationResult = !0,
                !this.hasConstraints())
                    return h.when();
                if (null != r || (r = this.getValue()),
                !this.needsValidation(r) && !0 !== i)
                    return h.when();
                var s = this._getGroupedConstraints()
                  , a = [];
                return h.each(s, function(t, e) {
                    var i = l.all(h.map(e, function(t) {
                        return n._validateConstraint(r, t)
                    }));
                    if (a.push(i),
                    "rejected" === i.state())
                        return !1
                }),
                l.all(a)
            }
        },
        _validateConstraint: function(t, e) {
            var i = this
              , n = e.validate(t, this);
            return !1 === n && (n = h.Deferred().reject()),
            l.all([n]).fail(function(t) {
                i.validationResult instanceof Array || (i.validationResult = []),
                i.validationResult.push({
                    assert: e,
                    errorMessage: "string" == typeof t && t
                })
            })
        },
        getValue: function() {
            var t;
            return null == (t = "function" == typeof this.options.value ? this.options.value(this) : void 0 !== this.options.value ? this.options.value : this.$element.val()) ? "" : this._handleWhitespace(t)
        },
        reset: function() {
            return this._resetUI(),
            this._trigger("reset")
        },
        destroy: function() {
            this._destroyUI(),
            this.$element.removeData("Parsley"),
            this.$element.removeData("FieldMultiple"),
            this._trigger("destroy")
        },
        refresh: function() {
            return this._refreshConstraints(),
            this
        },
        _refreshConstraints: function() {
            return this.actualizeOptions()._bindConstraints()
        },
        refreshConstraints: function() {
            return l.warnOnce("Parsley's refreshConstraints is deprecated. Please use refresh"),
            this.refresh()
        },
        addConstraint: function(t, e, i, n) {
            if (window.Parsley._validatorRegistry.validators[t]) {
                var r = new v(this,t,e,i,n);
                "undefined" !== this.constraintsByName[r.name] && this.removeConstraint(r.name),
                this.constraints.push(r),
                this.constraintsByName[r.name] = r
            }
            return this
        },
        removeConstraint: function(t) {
            for (var e = 0; e < this.constraints.length; e++)
                if (t === this.constraints[e].name) {
                    this.constraints.splice(e, 1);
                    break
                }
            return delete this.constraintsByName[t],
            this
        },
        updateConstraint: function(t, e, i) {
            return this.removeConstraint(t).addConstraint(t, e, i)
        },
        _bindConstraints: function() {
            for (var t = [], e = {}, i = 0; i < this.constraints.length; i++)
                !1 === this.constraints[i].isDomConstraint && (t.push(this.constraints[i]),
                e[this.constraints[i].name] = this.constraints[i]);
            for (var n in this.constraints = t,
            this.constraintsByName = e,
            this.options)
                this.addConstraint(n, this.options[n], void 0, !0);
            return this._bindHtml5Constraints()
        },
        _bindHtml5Constraints: function() {
            null !== this.element.getAttribute("required") && this.addConstraint("required", !0, void 0, !0),
            null !== this.element.getAttribute("pattern") && this.addConstraint("pattern", this.element.getAttribute("pattern"), void 0, !0);
            var t = this.element.getAttribute("min")
              , e = this.element.getAttribute("max");
            null !== t && null !== e ? this.addConstraint("range", [t, e], void 0, !0) : null !== t ? this.addConstraint("min", t, void 0, !0) : null !== e && this.addConstraint("max", e, void 0, !0),
            null !== this.element.getAttribute("minlength") && null !== this.element.getAttribute("maxlength") ? this.addConstraint("length", [this.element.getAttribute("minlength"), this.element.getAttribute("maxlength")], void 0, !0) : null !== this.element.getAttribute("minlength") ? this.addConstraint("minlength", this.element.getAttribute("minlength"), void 0, !0) : null !== this.element.getAttribute("maxlength") && this.addConstraint("maxlength", this.element.getAttribute("maxlength"), void 0, !0);
            var i = l.getType(this.element);
            return "number" === i ? this.addConstraint("type", ["number", {
                step: this.element.getAttribute("step") || "1",
                base: t || this.element.getAttribute("value")
            }], void 0, !0) : /^(email|url|range|date)$/i.test(i) ? this.addConstraint("type", i, void 0, !0) : this
        },
        _isRequired: function() {
            return void 0 !== this.constraintsByName.required && !1 !== this.constraintsByName.required.requirements
        },
        _trigger: function(t) {
            return this.trigger("field:" + t)
        },
        _handleWhitespace: function(t) {
            return !0 === this.options.trimValue && l.warnOnce('data-parsley-trim-value="true" is deprecated, please use data-parsley-whitespace="trim"'),
            "squish" === this.options.whitespace && (t = t.replace(/\s{2,}/g, " ")),
            "trim" !== this.options.whitespace && "squish" !== this.options.whitespace && !0 !== this.options.trimValue || (t = l.trimString(t)),
            t
        },
        _isDateInput: function() {
            var t = this.constraintsByName.type;
            return t && "date" === t.requirements
        },
        _getGroupedConstraints: function() {
            if (!1 === this.options.priorityEnabled)
                return [this.constraints];
            for (var t = [], e = {}, i = 0; i < this.constraints.length; i++) {
                var n = this.constraints[i].priority;
                e[n] || t.push(e[n] = []),
                e[n].push(this.constraints[i])
            }
            return t.sort(function(t, e) {
                return e[0].priority - t[0].priority
            }),
            t
        }
    };
    var b = y
      , x = function() {
        this.__class__ = "FieldMultiple"
    };
    x.prototype = {
        addElement: function(t) {
            return this.$elements.push(t),
            this
        },
        _refreshConstraints: function() {
            var t;
            if (this.constraints = [],
            "SELECT" === this.element.nodeName)
                return this.actualizeOptions()._bindConstraints(),
                this;
            for (var e = 0; e < this.$elements.length; e++)
                if (h("html").has(this.$elements[e]).length) {
                    t = this.$elements[e].data("FieldMultiple")._refreshConstraints().constraints;
                    for (var i = 0; i < t.length; i++)
                        this.addConstraint(t[i].name, t[i].requirements, t[i].priority, t[i].isDomConstraint)
                } else
                    this.$elements.splice(e, 1);
            return this
        },
        getValue: function() {
            if ("function" == typeof this.options.value)
                return this.options.value(this);
            if (void 0 !== this.options.value)
                return this.options.value;
            if ("INPUT" === this.element.nodeName) {
                var t = l.getType(this.element);
                if ("radio" === t)
                    return this._findRelated().filter(":checked").val() || "";
                if ("checkbox" === t) {
                    var e = [];
                    return this._findRelated().filter(":checked").each(function() {
                        e.push(h(this).val())
                    }),
                    e
                }
            }
            return "SELECT" === this.element.nodeName && null === this.$element.val() ? [] : this.$element.val()
        },
        _init: function() {
            return this.$elements = [this.$element],
            this
        }
    };
    var T = function(t, e, i) {
        this.element = t,
        this.$element = h(t);
        var n = this.$element.data("Parsley");
        if (n)
            return void 0 !== i && n.parent === window.Parsley && (n.parent = i,
            n._resetOptions(n.options)),
            "object" == typeof e && _extends(n.options, e),
            n;
        if (!this.$element.length)
            throw new Error("You must bind Parsley on an existing element.");
        if (void 0 !== i && "Form" !== i.__class__)
            throw new Error("Parent instance must be a Form instance");
        return this.parent = i || window.Parsley,
        this.init(e)
    };
    T.prototype = {
        init: function(t) {
            return this.__class__ = "Parsley",
            this.__version__ = "2.8.1",
            this.__id__ = l.generateID(),
            this._resetOptions(t),
            "FORM" === this.element.nodeName || l.checkAttr(this.element, this.options.namespace, "validate") && !this.$element.is(this.options.inputs) ? this.bind("parsleyForm") : this.isMultiple() ? this.handleMultiple() : this.bind("parsleyField")
        },
        isMultiple: function() {
            var t = l.getType(this.element);
            return "radio" === t || "checkbox" === t || "SELECT" === this.element.nodeName && null !== this.element.getAttribute("multiple")
        },
        handleMultiple: function() {
            var t, e, n = this;
            if (this.options.multiple = this.options.multiple || (t = this.element.getAttribute("name")) || this.element.getAttribute("id"),
            "SELECT" === this.element.nodeName && null !== this.element.getAttribute("multiple"))
                return this.options.multiple = this.options.multiple || this.__id__,
                this.bind("parsleyFieldMultiple");
            if (!this.options.multiple)
                return l.warn("To be bound by Parsley, a radio, a checkbox and a multiple select input must have either a name or a multiple option.", this.$element),
                this;
            this.options.multiple = this.options.multiple.replace(/(:|\.|\[|\]|\{|\}|\$)/g, ""),
            t && h('input[name="' + t + '"]').each(function(t, e) {
                var i = l.getType(e);
                "radio" !== i && "checkbox" !== i || e.setAttribute(n.options.namespace + "multiple", n.options.multiple)
            });
            for (var i = this._findRelated(), r = 0; r < i.length; r++)
                if (void 0 !== (e = h(i.get(r)).data("Parsley"))) {
                    this.$element.data("FieldMultiple") || e.addElement(this.$element);
                    break
                }
            return this.bind("parsleyField", !0),
            e || this.bind("parsleyFieldMultiple")
        },
        bind: function(t, e) {
            var i;
            switch (t) {
            case "parsleyForm":
                i = h.extend(new _(this.element,this.domOptions,this.options), new s, window.ParsleyExtend)._bindFields();
                break;
            case "parsleyField":
                i = h.extend(new b(this.element,this.domOptions,this.options,this.parent), new s, window.ParsleyExtend);
                break;
            case "parsleyFieldMultiple":
                i = h.extend(new b(this.element,this.domOptions,this.options,this.parent), new x, new s, window.ParsleyExtend)._init();
                break;
            default:
                throw new Error(t + "is not a supported Parsley type")
            }
            return this.options.multiple && l.setAttr(this.element, this.options.namespace, "multiple", this.options.multiple),
            void 0 !== e ? this.$element.data("FieldMultiple", i) : (this.$element.data("Parsley", i),
            i._actualizeTriggers(),
            i._trigger("init")),
            i
        }
    };
    var C = h.fn.jquery.split(".");
    if (parseInt(C[0]) <= 1 && parseInt(C[1]) < 8)
        throw "The loaded version of jQuery is too old. Please upgrade to 1.8.x or better.";
    C.forEach || l.warn("Parsley requires ES5 to run properly. Please include https://github.com/es-shims/es5-shim");
    var k = _extends(new s, {
        element: document,
        $element: h(document),
        actualizeOptions: null,
        _resetOptions: null,
        Factory: T,
        version: "2.8.1"
    });
    _extends(b.prototype, g.Field, s.prototype),
    _extends(_.prototype, g.Form, s.prototype),
    _extends(T.prototype, s.prototype),
    h.fn.parsley = h.fn.psly = function(t) {
        if (1 < this.length) {
            var e = [];
            return this.each(function() {
                e.push(h(this).parsley(t))
            }),
            e
        }
        if (0 != this.length)
            return new T(this[0],t)
    }
    ,
    void 0 === window.ParsleyExtend && (window.ParsleyExtend = {}),
    k.options = _extends(l.objectCreate(r), window.ParsleyConfig),
    window.ParsleyConfig = k.options,
    window.Parsley = window.psly = k,
    k.Utils = l,
    window.ParsleyUtils = {},
    h.each(l, function(t, e) {
        "function" == typeof e && (window.ParsleyUtils[t] = function() {
            return l.warnOnce("Accessing `window.ParsleyUtils` is deprecated. Use `window.Parsley.Utils` instead."),
            l[t].apply(l, arguments)
        }
        )
    });
    var S = window.Parsley._validatorRegistry = new u(window.ParsleyConfig.validators,window.ParsleyConfig.i18n);
    window.ParsleyValidator = {},
    h.each("setLocale addCatalog addMessage addMessages getErrorMessage formatMessage addValidator updateValidator removeValidator hasValidator".split(" "), function(t, e) {
        window.Parsley[e] = function() {
            return S[e].apply(S, arguments)
        }
        ,
        window.ParsleyValidator[e] = function() {
            var t;
            return l.warnOnce("Accessing the method '" + e + "' through Validator is deprecated. Simply call 'window.Parsley." + e + "(...)'"),
            (t = window.Parsley)[e].apply(t, arguments)
        }
    }),
    window.Parsley.UI = g,
    window.ParsleyUI = {
        removeError: function(t, e, i) {
            var n = !0 !== i;
            return l.warnOnce("Accessing UI is deprecated. Call 'removeError' on the instance directly. Please comment in issue 1073 as to your need to call this method."),
            t.removeError(e, {
                updateClass: n
            })
        },
        getErrorsMessages: function(t) {
            return l.warnOnce("Accessing UI is deprecated. Call 'getErrorsMessages' on the instance directly."),
            t.getErrorsMessages()
        }
    },
    h.each("addError updateError".split(" "), function(t, s) {
        window.ParsleyUI[s] = function(t, e, i, n, r) {
            var o = !0 !== r;
            return l.warnOnce("Accessing UI is deprecated. Call '" + s + "' on the instance directly. Please comment in issue 1073 as to your need to call this method."),
            t[s](e, {
                message: i,
                assert: n,
                updateClass: o
            })
        }
    }),
    !1 !== window.ParsleyConfig.autoBind && h(function() {
        h("[data-parsley-validate]").length && h("[data-parsley-validate]").parsley()
    });
    var $ = h({})
      , D = function() {
        l.warnOnce("Parsley's pubsub module is deprecated; use the 'on' and 'off' methods on parsley instances or window.Parsley")
    };
    return h.listen = function(t, e) {
        var i;
        if (D(),
        "object" == typeof arguments[1] && "function" == typeof arguments[2] && (i = arguments[1],
        e = arguments[2]),
        "function" != typeof e)
            throw new Error("Wrong parameters");
        window.Parsley.on(o(t), n(e, i))
    }
    ,
    h.listenTo = function(t, e, i) {
        if (D(),
        !(t instanceof b || t instanceof _))
            throw new Error("Must give Parsley instance");
        if ("string" != typeof e || "function" != typeof i)
            throw new Error("Wrong parameters");
        t.on(o(e), n(i))
    }
    ,
    h.unsubscribe = function(t, e) {
        if (D(),
        "string" != typeof t || "function" != typeof e)
            throw new Error("Wrong arguments");
        window.Parsley.off(o(t), e.parsleyAdaptedCallback)
    }
    ,
    h.unsubscribeTo = function(t, e) {
        if (D(),
        !(t instanceof b || t instanceof _))
            throw new Error("Must give Parsley instance");
        t.off(o(e))
    }
    ,
    h.unsubscribeAll = function(e) {
        D(),
        window.Parsley.off(o(e)),
        h("form,input,textarea,select").each(function() {
            var t = h(this).data("Parsley");
            t && t.off(o(e))
        })
    }
    ,
    h.emit = function(t, e) {
        var i;
        D();
        var n = e instanceof b || e instanceof _
          , r = Array.prototype.slice.call(arguments, n ? 2 : 1);
        r.unshift(o(t)),
        n || (e = window.Parsley),
        (i = e).trigger.apply(i, _toConsumableArray(r))
    }
    ,
    h.extend(!0, k, {
        asyncValidators: {
            default: {
                fn: function(t) {
                    return 200 <= t.status && t.status < 300
                },
                url: !1
            },
            reverse: {
                fn: function(t) {
                    return t.status < 200 || 300 <= t.status
                },
                url: !1
            }
        },
        addAsyncValidator: function(t, e, i, n) {
            return k.asyncValidators[t] = {
                fn: e,
                url: i || !1,
                options: n || {}
            },
            this
        }
    }),
    k.addValidator("remote", {
        requirementType: {
            "": "string",
            validator: "string",
            reverse: "boolean",
            options: "object"
        },
        validateString: function(t, e, i, n) {
            var r, o, s = {}, a = i.validator || (!0 === i.reverse ? "reverse" : "default");
            if (void 0 === k.asyncValidators[a])
                throw new Error("Calling an undefined async validator: `" + a + "`");
            -1 < (e = k.asyncValidators[a].url || e).indexOf("{value}") ? e = e.replace("{value}", encodeURIComponent(t)) : s[n.element.getAttribute("name") || n.element.getAttribute("id")] = t;
            var l = h.extend(!0, i.options || {}, k.asyncValidators[a].options);
            r = h.extend(!0, {}, {
                url: e,
                data: s,
                type: "GET"
            }, l),
            n.trigger("field:ajaxoptions", n, r),
            o = h.param(r),
            void 0 === k._remoteCache && (k._remoteCache = {});
            var u = k._remoteCache[o] = k._remoteCache[o] || h.ajax(r)
              , c = function() {
                var t = k.asyncValidators[a].fn.call(n, u, e, i);
                return t || (t = h.Deferred().reject()),
                h.when(t)
            };
            return u.then(c, c)
        },
        priority: -1
    }),
    k.on("form:submit", function() {
        k._remoteCache = {}
    }),
    s.prototype.addAsyncValidator = function() {
        return l.warnOnce("Accessing the method `addAsyncValidator` through an instance is deprecated. Simply call `Parsley.addAsyncValidator(...)`"),
        k.addAsyncValidator.apply(k, arguments)
    }
    ,
    k.addMessages("en", {
        defaultMessage: "This value seems to be invalid.",
        type: {
            email: "This value should be a valid email.",
            url: "This value should be a valid url.",
            number: "This value should be a valid number.",
            integer: "This value should be a valid integer.",
            digits: "This value should be digits.",
            alphanum: "This value should be alphanumeric."
        },
        notblank: "This value should not be blank.",
        required: "This value is required.",
        pattern: "This value seems to be invalid.",
        min: "This value should be greater than or equal to %s.",
        max: "This value should be lower than or equal to %s.",
        range: "This value should be between %s and %s.",
        minlength: "This value is too short. It should have %s characters or more.",
        maxlength: "This value is too long. It should have %s characters or fewer.",
        length: "This value length is invalid. It should be between %s and %s characters long.",
        mincheck: "You must select at least %s choices.",
        maxcheck: "You must select %s choices or fewer.",
        check: "You must select between %s and %s choices.",
        equalto: "This value should be the same."
    }),
    k.setLocale("en"),
    (new function() {
        var n = this
          , r = window || global;
        _extends(this, {
            isNativeEvent: function(t) {
                return t.originalEvent && !1 !== t.originalEvent.isTrusted
            },
            fakeInputEvent: function(t) {
                n.isNativeEvent(t) && h(t.target).trigger("input")
            },
            misbehaves: function(t) {
                n.isNativeEvent(t) && (n.behavesOk(t),
                h(document).on("change.inputevent", t.data.selector, n.fakeInputEvent),
                n.fakeInputEvent(t))
            },
            behavesOk: function(t) {
                n.isNativeEvent(t) && h(document).off("input.inputevent", t.data.selector, n.behavesOk).off("change.inputevent", t.data.selector, n.misbehaves)
            },
            install: function() {
                if (!r.inputEventPatched) {
                    r.inputEventPatched = "0.0.3";
                    for (var t = ["select", 'input[type="checkbox"]', 'input[type="radio"]', 'input[type="file"]'], e = 0; e < t.length; e++) {
                        var i = t[e];
                        h(document).on("input.inputevent", i, {
                            selector: i
                        }, n.behavesOk).on("change.inputevent", i, {
                            selector: i
                        }, n.misbehaves)
                    }
                }
            },
            uninstall: function() {
                delete r.inputEventPatched,
                h(document).off(".inputevent")
            }
        })
    }
    ).install(),
    k
}),
function(l, i, r, o) {
    function u(t, e) {
        this.settings = null,
        this.options = l.extend({}, u.Defaults, e),
        this.$element = l(t),
        this._handlers = {},
        this._plugins = {},
        this._supress = {},
        this._current = null,
        this._speed = null,
        this._coordinates = [],
        this._breakpoint = null,
        this._width = null,
        this._items = [],
        this._clones = [],
        this._mergers = [],
        this._widths = [],
        this._invalidated = {},
        this._pipe = [],
        this._drag = {
            time: null,
            target: null,
            pointer: null,
            stage: {
                start: null,
                current: null
            },
            direction: null
        },
        this._states = {
            current: {},
            tags: {
                initializing: ["busy"],
                animating: ["busy"],
                dragging: ["interacting"]
            }
        },
        l.each(["onResize", "onThrottledResize"], l.proxy(function(t, e) {
            this._handlers[e] = l.proxy(this[e], this)
        }, this)),
        l.each(u.Plugins, l.proxy(function(t, e) {
            this._plugins[t.charAt(0).toLowerCase() + t.slice(1)] = new e(this)
        }, this)),
        l.each(u.Workers, l.proxy(function(t, e) {
            this._pipe.push({
                filter: e.filter,
                run: l.proxy(e.run, this)
            })
        }, this)),
        this.setup(),
        this.initialize()
    }
    u.Defaults = {
        items: 3,
        loop: !1,
        center: !1,
        rewind: !1,
        mouseDrag: !0,
        touchDrag: !0,
        pullDrag: !0,
        freeDrag: !1,
        margin: 0,
        stagePadding: 0,
        merge: !1,
        mergeFit: !0,
        autoWidth: !1,
        startPosition: 0,
        rtl: !1,
        smartSpeed: 250,
        fluidSpeed: !1,
        dragEndSpeed: !1,
        responsive: {},
        responsiveRefreshRate: 200,
        responsiveBaseElement: i,
        fallbackEasing: "swing",
        info: !1,
        nestedItemSelector: !1,
        itemElement: "div",
        stageElement: "div",
        refreshClass: "owl-refresh",
        loadedClass: "owl-loaded",
        loadingClass: "owl-loading",
        rtlClass: "owl-rtl",
        responsiveClass: "owl-responsive",
        dragClass: "owl-drag",
        itemClass: "owl-item",
        stageClass: "owl-stage",
        stageOuterClass: "owl-stage-outer",
        grabClass: "owl-grab"
    },
    u.Width = {
        Default: "default",
        Inner: "inner",
        Outer: "outer"
    },
    u.Type = {
        Event: "event",
        State: "state"
    },
    u.Plugins = {},
    u.Workers = [{
        filter: ["width", "settings"],
        run: function() {
            this._width = this.$element.width()
        }
    }, {
        filter: ["width", "items", "settings"],
        run: function(t) {
            t.current = this._items && this._items[this.relative(this._current)]
        }
    }, {
        filter: ["items", "settings"],
        run: function() {
            this.$stage.children(".cloned").remove()
        }
    }, {
        filter: ["width", "items", "settings"],
        run: function(t) {
            var e = this.settings.margin || ""
              , i = !this.settings.autoWidth
              , n = this.settings.rtl
              , r = {
                width: "auto",
                "margin-left": n ? e : "",
                "margin-right": n ? "" : e
            };
            !i && this.$stage.children().css(r),
            t.css = r
        }
    }, {
        filter: ["width", "items", "settings"],
        run: function(t) {
            var e = (this.width() / this.settings.items).toFixed(3) - this.settings.margin
              , i = null
              , n = this._items.length
              , r = !this.settings.autoWidth
              , o = [];
            for (t.items = {
                merge: !1,
                width: e
            }; n--; )
                i = this._mergers[n],
                i = this.settings.mergeFit && Math.min(i, this.settings.items) || i,
                t.items.merge = 1 < i || t.items.merge,
                o[n] = r ? e * i : this._items[n].width();
            this._widths = o
        }
    }, {
        filter: ["items", "settings"],
        run: function() {
            var t = []
              , e = this._items
              , i = this.settings
              , n = Math.max(2 * i.items, 4)
              , r = 2 * Math.ceil(e.length / 2)
              , o = i.loop && e.length ? i.rewind ? n : Math.max(n, r) : 0
              , s = ""
              , a = "";
            for (o /= 2; 0 < o; )
                t.push(this.normalize(t.length / 2, !0)),
                s += e[t[t.length - 1]][0].outerHTML,
                t.push(this.normalize(e.length - 1 - (t.length - 1) / 2, !0)),
                a = e[t[t.length - 1]][0].outerHTML + a,
                o -= 1;
            this._clones = t,
            l(s).addClass("cloned").appendTo(this.$stage),
            l(a).addClass("cloned").prependTo(this.$stage)
        }
    }, {
        filter: ["width", "items", "settings"],
        run: function() {
            for (var t = this.settings.rtl ? 1 : -1, e = this._clones.length + this._items.length, i = -1, n = 0, r = 0, o = []; ++i < e; )
                n = o[i - 1] || 0,
                r = this._widths[this.relative(i)] + this.settings.margin,
                o.push(n + r * t);
            this._coordinates = o
        }
    }, {
        filter: ["width", "items", "settings"],
        run: function() {
            var t = this.settings.stagePadding
              , e = this._coordinates
              , i = {
                width: Math.ceil(Math.abs(e[e.length - 1])) + 2 * t,
                "padding-left": t || "",
                "padding-right": t || ""
            };
            this.$stage.css(i)
        }
    }, {
        filter: ["width", "items", "settings"],
        run: function(t) {
            var e = this._coordinates.length
              , i = !this.settings.autoWidth
              , n = this.$stage.children();
            if (i && t.items.merge)
                for (; e--; )
                    t.css.width = this._widths[this.relative(e)],
                    n.eq(e).css(t.css);
            else
                i && (t.css.width = t.items.width,
                n.css(t.css))
        }
    }, {
        filter: ["items"],
        run: function() {
            this._coordinates.length < 1 && this.$stage.removeAttr("style")
        }
    }, {
        filter: ["width", "items", "settings"],
        run: function(t) {
            t.current = t.current ? this.$stage.children().index(t.current) : 0,
            t.current = Math.max(this.minimum(), Math.min(this.maximum(), t.current)),
            this.reset(t.current)
        }
    }, {
        filter: ["position"],
        run: function() {
            this.animate(this.coordinates(this._current))
        }
    }, {
        filter: ["width", "position", "items", "settings"],
        run: function() {
            var t, e, i, n, r = this.settings.rtl ? 1 : -1, o = 2 * this.settings.stagePadding, s = this.coordinates(this.current()) + o, a = s + this.width() * r, l = [];
            for (i = 0,
            n = this._coordinates.length; i < n; i++)
                t = this._coordinates[i - 1] || 0,
                e = Math.abs(this._coordinates[i]) + o * r,
                (this.op(t, "<=", s) && this.op(t, ">", a) || this.op(e, "<", s) && this.op(e, ">", a)) && l.push(i);
            this.$stage.children(".active").removeClass("active"),
            this.$stage.children(":eq(" + l.join("), :eq(") + ")").addClass("active"),
            this.$stage.children(".center").removeClass("center"),
            this.settings.center && this.$stage.children().eq(this.current()).addClass("center")
        }
    }],
    u.prototype.initialize = function() {
        var t, e, i;
        (this.enter("initializing"),
        this.trigger("initialize"),
        this.$element.toggleClass(this.settings.rtlClass, this.settings.rtl),
        this.settings.autoWidth && !this.is("pre-loading")) && (t = this.$element.find("img"),
        e = this.settings.nestedItemSelector ? "." + this.settings.nestedItemSelector : o,
        i = this.$element.children(e).width(),
        t.length && i <= 0 && this.preloadAutoWidthImages(t));
        this.$element.addClass(this.options.loadingClass),
        this.$stage = l("<" + this.settings.stageElement + ' class="' + this.settings.stageClass + '"/>').wrap('<div class="' + this.settings.stageOuterClass + '"/>'),
        this.$element.append(this.$stage.parent()),
        this.replace(this.$element.children().not(this.$stage.parent())),
        this.$element.is(":visible") ? this.refresh() : this.invalidate("width"),
        this.$element.removeClass(this.options.loadingClass).addClass(this.options.loadedClass),
        this.registerEventHandlers(),
        this.leave("initializing"),
        this.trigger("initialized")
    }
    ,
    u.prototype.setup = function() {
        var e = this.viewport()
          , t = this.options.responsive
          , i = -1
          , n = null;
        t ? (l.each(t, function(t) {
            t <= e && i < t && (i = Number(t))
        }),
        "function" == typeof (n = l.extend({}, this.options, t[i])).stagePadding && (n.stagePadding = n.stagePadding()),
        delete n.responsive,
        n.responsiveClass && this.$element.attr("class", this.$element.attr("class").replace(new RegExp("(" + this.options.responsiveClass + "-)\\S+\\s","g"), "$1" + i))) : n = l.extend({}, this.options),
        this.trigger("change", {
            property: {
                name: "settings",
                value: n
            }
        }),
        this._breakpoint = i,
        this.settings = n,
        this.invalidate("settings"),
        this.trigger("changed", {
            property: {
                name: "settings",
                value: this.settings
            }
        })
    }
    ,
    u.prototype.optionsLogic = function() {
        this.settings.autoWidth && (this.settings.stagePadding = !1,
        this.settings.merge = !1)
    }
    ,
    u.prototype.prepare = function(t) {
        var e = this.trigger("prepare", {
            content: t
        });
        return e.data || (e.data = l("<" + this.settings.itemElement + "/>").addClass(this.options.itemClass).append(t)),
        this.trigger("prepared", {
            content: e.data
        }),
        e.data
    }
    ,
    u.prototype.update = function() {
        for (var t = 0, e = this._pipe.length, i = l.proxy(function(t) {
            return this[t]
        }, this._invalidated), n = {}; t < e; )
            (this._invalidated.all || 0 < l.grep(this._pipe[t].filter, i).length) && this._pipe[t].run(n),
            t++;
        this._invalidated = {},
        !this.is("valid") && this.enter("valid")
    }
    ,
    u.prototype.width = function(t) {
        switch (t = t || u.Width.Default) {
        case u.Width.Inner:
        case u.Width.Outer:
            return this._width;
        default:
            return this._width - 2 * this.settings.stagePadding + this.settings.margin
        }
    }
    ,
    u.prototype.refresh = function() {
        this.enter("refreshing"),
        this.trigger("refresh"),
        this.setup(),
        this.optionsLogic(),
        this.$element.addClass(this.options.refreshClass),
        this.update(),
        this.$element.removeClass(this.options.refreshClass),
        this.leave("refreshing"),
        this.trigger("refreshed")
    }
    ,
    u.prototype.onThrottledResize = function() {
        i.clearTimeout(this.resizeTimer),
        this.resizeTimer = i.setTimeout(this._handlers.onResize, this.settings.responsiveRefreshRate)
    }
    ,
    u.prototype.onResize = function() {
        return !!this._items.length && (this._width !== this.$element.width() && (!!this.$element.is(":visible") && (this.enter("resizing"),
        this.trigger("resize").isDefaultPrevented() ? (this.leave("resizing"),
        !1) : (this.invalidate("width"),
        this.refresh(),
        this.leave("resizing"),
        void this.trigger("resized")))))
    }
    ,
    u.prototype.registerEventHandlers = function() {
        l.support.transition && this.$stage.on(l.support.transition.end + ".owl.core", l.proxy(this.onTransitionEnd, this)),
        !1 !== this.settings.responsive && this.on(i, "resize", this._handlers.onThrottledResize),
        this.settings.mouseDrag && (this.$element.addClass(this.options.dragClass),
        this.$stage.on("mousedown.owl.core", l.proxy(this.onDragStart, this)),
        this.$stage.on("dragstart.owl.core selectstart.owl.core", function() {
            return !1
        })),
        this.settings.touchDrag && (this.$stage.on("touchstart.owl.core", l.proxy(this.onDragStart, this)),
        this.$stage.on("touchcancel.owl.core", l.proxy(this.onDragEnd, this)))
    }
    ,
    u.prototype.onDragStart = function(t) {
        var e = null;
        3 !== t.which && (e = l.support.transform ? {
            x: (e = this.$stage.css("transform").replace(/.*\(|\)| /g, "").split(","))[16 === e.length ? 12 : 4],
            y: e[16 === e.length ? 13 : 5]
        } : (e = this.$stage.position(),
        {
            x: this.settings.rtl ? e.left + this.$stage.width() - this.width() + this.settings.margin : e.left,
            y: e.top
        }),
        this.is("animating") && (l.support.transform ? this.animate(e.x) : this.$stage.stop(),
        this.invalidate("position")),
        this.$element.toggleClass(this.options.grabClass, "mousedown" === t.type),
        this.speed(0),
        this._drag.time = (new Date).getTime(),
        this._drag.target = l(t.target),
        this._drag.stage.start = e,
        this._drag.stage.current = e,
        this._drag.pointer = this.pointer(t),
        l(r).on("mouseup.owl.core touchend.owl.core", l.proxy(this.onDragEnd, this)),
        l(r).one("mousemove.owl.core touchmove.owl.core", l.proxy(function(t) {
            var e = this.difference(this._drag.pointer, this.pointer(t));
            l(r).on("mousemove.owl.core touchmove.owl.core", l.proxy(this.onDragMove, this)),
            Math.abs(e.x) < Math.abs(e.y) && this.is("valid") || (t.preventDefault(),
            this.enter("dragging"),
            this.trigger("drag"))
        }, this)))
    }
    ,
    u.prototype.onDragMove = function(t) {
        var e = null
          , i = null
          , n = null
          , r = this.difference(this._drag.pointer, this.pointer(t))
          , o = this.difference(this._drag.stage.start, r);
        this.is("dragging") && (t.preventDefault(),
        this.settings.loop ? (e = this.coordinates(this.minimum()),
        i = this.coordinates(this.maximum() + 1) - e,
        o.x = ((o.x - e) % i + i) % i + e) : (e = this.settings.rtl ? this.coordinates(this.maximum()) : this.coordinates(this.minimum()),
        i = this.settings.rtl ? this.coordinates(this.minimum()) : this.coordinates(this.maximum()),
        n = this.settings.pullDrag ? -1 * r.x / 5 : 0,
        o.x = Math.max(Math.min(o.x, e + n), i + n)),
        this._drag.stage.current = o,
        this.animate(o.x))
    }
    ,
    u.prototype.onDragEnd = function(t) {
        var e = this.difference(this._drag.pointer, this.pointer(t))
          , i = this._drag.stage.current
          , n = 0 < e.x ^ this.settings.rtl ? "left" : "right";
        l(r).off(".owl.core"),
        this.$element.removeClass(this.options.grabClass),
        (0 !== e.x && this.is("dragging") || !this.is("valid")) && (this.speed(this.settings.dragEndSpeed || this.settings.smartSpeed),
        this.current(this.closest(i.x, 0 !== e.x ? n : this._drag.direction)),
        this.invalidate("position"),
        this.update(),
        this._drag.direction = n,
        (3 < Math.abs(e.x) || 300 < (new Date).getTime() - this._drag.time) && this._drag.target.one("click.owl.core", function() {
            return !1
        })),
        this.is("dragging") && (this.leave("dragging"),
        this.trigger("dragged"))
    }
    ,
    u.prototype.closest = function(i, n) {
        var r = -1
          , o = this.width()
          , s = this.coordinates();
        return this.settings.freeDrag || l.each(s, l.proxy(function(t, e) {
            return "left" === n && e - 30 < i && i < e + 30 ? r = t : "right" === n && e - o - 30 < i && i < e - o + 30 ? r = t + 1 : this.op(i, "<", e) && this.op(i, ">", s[t + 1] || e - o) && (r = "left" === n ? t + 1 : t),
            -1 === r
        }, this)),
        this.settings.loop || (this.op(i, ">", s[this.minimum()]) ? r = i = this.minimum() : this.op(i, "<", s[this.maximum()]) && (r = i = this.maximum())),
        r
    }
    ,
    u.prototype.animate = function(t) {
        var e = 0 < this.speed();
        this.is("animating") && this.onTransitionEnd(),
        e && (this.enter("animating"),
        this.trigger("translate")),
        l.support.transform3d && l.support.transition ? this.$stage.css({
            transform: "translate3d(" + t + "px,0px,0px)",
            transition: this.speed() / 1e3 + "s"
        }) : e ? this.$stage.animate({
            left: t + "px"
        }, this.speed(), this.settings.fallbackEasing, l.proxy(this.onTransitionEnd, this)) : this.$stage.css({
            left: t + "px"
        })
    }
    ,
    u.prototype.is = function(t) {
        return this._states.current[t] && 0 < this._states.current[t]
    }
    ,
    u.prototype.current = function(t) {
        if (t === o)
            return this._current;
        if (0 === this._items.length)
            return o;
        if (t = this.normalize(t),
        this._current !== t) {
            var e = this.trigger("change", {
                property: {
                    name: "position",
                    value: t
                }
            });
            e.data !== o && (t = this.normalize(e.data)),
            this._current = t,
            this.invalidate("position"),
            this.trigger("changed", {
                property: {
                    name: "position",
                    value: this._current
                }
            })
        }
        return this._current
    }
    ,
    u.prototype.invalidate = function(t) {
        return "string" === l.type(t) && (this._invalidated[t] = !0,
        this.is("valid") && this.leave("valid")),
        l.map(this._invalidated, function(t, e) {
            return e
        })
    }
    ,
    u.prototype.reset = function(t) {
        (t = this.normalize(t)) !== o && (this._speed = 0,
        this._current = t,
        this.suppress(["translate", "translated"]),
        this.animate(this.coordinates(t)),
        this.release(["translate", "translated"]))
    }
    ,
    u.prototype.normalize = function(t, e) {
        var i = this._items.length
          , n = e ? 0 : this._clones.length;
        return !this.isNumeric(t) || i < 1 ? t = o : (t < 0 || i + n <= t) && (t = ((t - n / 2) % i + i) % i + n / 2),
        t
    }
    ,
    u.prototype.relative = function(t) {
        return t -= this._clones.length / 2,
        this.normalize(t, !0)
    }
    ,
    u.prototype.maximum = function(t) {
        var e, i, n, r = this.settings, o = this._coordinates.length;
        if (r.loop)
            o = this._clones.length / 2 + this._items.length - 1;
        else if (r.autoWidth || r.merge) {
            if (e = this._items.length)
                for (i = this._items[--e].width(),
                n = this.$element.width(); e-- && !(n < (i += this._items[e].width() + this.settings.margin)); )
                    ;
            o = e + 1
        } else
            o = r.center ? this._items.length - 1 : this._items.length - r.items;
        return t && (o -= this._clones.length / 2),
        Math.max(o, 0)
    }
    ,
    u.prototype.minimum = function(t) {
        return t ? 0 : this._clones.length / 2
    }
    ,
    u.prototype.items = function(t) {
        return t === o ? this._items.slice() : (t = this.normalize(t, !0),
        this._items[t])
    }
    ,
    u.prototype.mergers = function(t) {
        return t === o ? this._mergers.slice() : (t = this.normalize(t, !0),
        this._mergers[t])
    }
    ,
    u.prototype.clones = function(i) {
        var e = this._clones.length / 2
          , n = e + this._items.length
          , r = function(t) {
            return t % 2 == 0 ? n + t / 2 : e - (t + 1) / 2
        };
        return i === o ? l.map(this._clones, function(t, e) {
            return r(e)
        }) : l.map(this._clones, function(t, e) {
            return t === i ? r(e) : null
        })
    }
    ,
    u.prototype.speed = function(t) {
        return t !== o && (this._speed = t),
        this._speed
    }
    ,
    u.prototype.coordinates = function(t) {
        var e, i = 1, n = t - 1;
        return t === o ? l.map(this._coordinates, l.proxy(function(t, e) {
            return this.coordinates(e)
        }, this)) : (this.settings.center ? (this.settings.rtl && (i = -1,
        n = t + 1),
        e = this._coordinates[t],
        e += (this.width() - e + (this._coordinates[n] || 0)) / 2 * i) : e = this._coordinates[n] || 0,
        e = Math.ceil(e))
    }
    ,
    u.prototype.duration = function(t, e, i) {
        return 0 === i ? 0 : Math.min(Math.max(Math.abs(e - t), 1), 6) * Math.abs(i || this.settings.smartSpeed)
    }
    ,
    u.prototype.to = function(t, e) {
        var i = this.current()
          , n = null
          , r = t - this.relative(i)
          , o = (0 < r) - (r < 0)
          , s = this._items.length
          , a = this.minimum()
          , l = this.maximum();
        this.settings.loop ? (!this.settings.rewind && Math.abs(r) > s / 2 && (r += -1 * o * s),
        (n = (((t = i + r) - a) % s + s) % s + a) !== t && n - r <= l && 0 < n - r && (i = n - r,
        t = n,
        this.reset(i))) : t = this.settings.rewind ? (t % (l += 1) + l) % l : Math.max(a, Math.min(l, t)),
        this.speed(this.duration(i, t, e)),
        this.current(t),
        this.$element.is(":visible") && this.update()
    }
    ,
    u.prototype.next = function(t) {
        t = t || !1,
        this.to(this.relative(this.current()) + 1, t)
    }
    ,
    u.prototype.prev = function(t) {
        t = t || !1,
        this.to(this.relative(this.current()) - 1, t)
    }
    ,
    u.prototype.onTransitionEnd = function(t) {
        return (t === o || (t.stopPropagation(),
        (t.target || t.srcElement || t.originalTarget) === this.$stage.get(0))) && (this.leave("animating"),
        void this.trigger("translated"))
    }
    ,
    u.prototype.viewport = function() {
        var t;
        return this.options.responsiveBaseElement !== i ? t = l(this.options.responsiveBaseElement).width() : i.innerWidth ? t = i.innerWidth : r.documentElement && r.documentElement.clientWidth ? t = r.documentElement.clientWidth : console.warn("Can not detect viewport width."),
        t
    }
    ,
    u.prototype.replace = function(t) {
        this.$stage.empty(),
        this._items = [],
        t && (t = t instanceof jQuery ? t : l(t)),
        this.settings.nestedItemSelector && (t = t.find("." + this.settings.nestedItemSelector)),
        t.filter(function() {
            return 1 === this.nodeType
        }).each(l.proxy(function(t, e) {
            e = this.prepare(e),
            this.$stage.append(e),
            this._items.push(e),
            this._mergers.push(1 * e.find("[data-merge]").addBack("[data-merge]").attr("data-merge") || 1)
        }, this)),
        this.reset(this.isNumeric(this.settings.startPosition) ? this.settings.startPosition : 0),
        this.invalidate("items")
    }
    ,
    u.prototype.add = function(t, e) {
        var i = this.relative(this._current);
        e = e === o ? this._items.length : this.normalize(e, !0),
        t = t instanceof jQuery ? t : l(t),
        this.trigger("add", {
            content: t,
            position: e
        }),
        t = this.prepare(t),
        0 === this._items.length || e === this._items.length ? (0 === this._items.length && this.$stage.append(t),
        0 !== this._items.length && this._items[e - 1].after(t),
        this._items.push(t),
        this._mergers.push(1 * t.find("[data-merge]").addBack("[data-merge]").attr("data-merge") || 1)) : (this._items[e].before(t),
        this._items.splice(e, 0, t),
        this._mergers.splice(e, 0, 1 * t.find("[data-merge]").addBack("[data-merge]").attr("data-merge") || 1)),
        this._items[i] && this.reset(this._items[i].index()),
        this.invalidate("items"),
        this.trigger("added", {
            content: t,
            position: e
        })
    }
    ,
    u.prototype.remove = function(t) {
        (t = this.normalize(t, !0)) !== o && (this.trigger("remove", {
            content: this._items[t],
            position: t
        }),
        this._items[t].remove(),
        this._items.splice(t, 1),
        this._mergers.splice(t, 1),
        this.invalidate("items"),
        this.trigger("removed", {
            content: null,
            position: t
        }))
    }
    ,
    u.prototype.preloadAutoWidthImages = function(t) {
        t.each(l.proxy(function(t, e) {
            this.enter("pre-loading"),
            e = l(e),
            l(new Image).one("load", l.proxy(function(t) {
                e.attr("src", t.target.src),
                e.css("opacity", 1),
                this.leave("pre-loading"),
                !this.is("pre-loading") && !this.is("initializing") && this.refresh()
            }, this)).attr("src", e.attr("src") || e.attr("data-src") || e.attr("data-src-retina"))
        }, this))
    }
    ,
    u.prototype.destroy = function() {
        for (var t in this.$element.off(".owl.core"),
        this.$stage.off(".owl.core"),
        l(r).off(".owl.core"),
        !1 !== this.settings.responsive && (i.clearTimeout(this.resizeTimer),
        this.off(i, "resize", this._handlers.onThrottledResize)),
        this._plugins)
            this._plugins[t].destroy();
        this.$stage.children(".cloned").remove(),
        this.$stage.unwrap(),
        this.$stage.children().contents().unwrap(),
        this.$stage.children().unwrap(),
        this.$stage.remove(),
        this.$element.removeClass(this.options.refreshClass).removeClass(this.options.loadingClass).removeClass(this.options.loadedClass).removeClass(this.options.rtlClass).removeClass(this.options.dragClass).removeClass(this.options.grabClass).attr("class", this.$element.attr("class").replace(new RegExp(this.options.responsiveClass + "-\\S+\\s","g"), "")).removeData("owl.carousel")
    }
    ,
    u.prototype.op = function(t, e, i) {
        var n = this.settings.rtl;
        switch (e) {
        case "<":
            return n ? i < t : t < i;
        case ">":
            return n ? t < i : i < t;
        case ">=":
            return n ? t <= i : i <= t;
        case "<=":
            return n ? i <= t : t <= i
        }
    }
    ,
    u.prototype.on = function(t, e, i, n) {
        t.addEventListener ? t.addEventListener(e, i, n) : t.attachEvent && t.attachEvent("on" + e, i)
    }
    ,
    u.prototype.off = function(t, e, i, n) {
        t.removeEventListener ? t.removeEventListener(e, i, n) : t.detachEvent && t.detachEvent("on" + e, i)
    }
    ,
    u.prototype.trigger = function(t, e, i, n, r) {
        var o = {
            item: {
                count: this._items.length,
                index: this.current()
            }
        }
          , s = l.camelCase(l.grep(["on", t, i], function(t) {
            return t
        }).join("-").toLowerCase())
          , a = l.Event([t, "owl", i || "carousel"].join(".").toLowerCase(), l.extend({
            relatedTarget: this
        }, o, e));
        return this._supress[t] || (l.each(this._plugins, function(t, e) {
            e.onTrigger && e.onTrigger(a)
        }),
        this.register({
            type: u.Type.Event,
            name: t
        }),
        this.$element.trigger(a),
        this.settings && "function" == typeof this.settings[s] && this.settings[s].call(this, a)),
        a
    }
    ,
    u.prototype.enter = function(t) {
        l.each([t].concat(this._states.tags[t] || []), l.proxy(function(t, e) {
            this._states.current[e] === o && (this._states.current[e] = 0),
            this._states.current[e]++
        }, this))
    }
    ,
    u.prototype.leave = function(t) {
        l.each([t].concat(this._states.tags[t] || []), l.proxy(function(t, e) {
            this._states.current[e]--
        }, this))
    }
    ,
    u.prototype.register = function(i) {
        if (i.type === u.Type.Event) {
            if (l.event.special[i.name] || (l.event.special[i.name] = {}),
            !l.event.special[i.name].owl) {
                var e = l.event.special[i.name]._default;
                l.event.special[i.name]._default = function(t) {
                    return !e || !e.apply || t.namespace && -1 !== t.namespace.indexOf("owl") ? t.namespace && -1 < t.namespace.indexOf("owl") : e.apply(this, arguments)
                }
                ,
                l.event.special[i.name].owl = !0
            }
        } else
            i.type === u.Type.State && (this._states.tags[i.name] ? this._states.tags[i.name] = this._states.tags[i.name].concat(i.tags) : this._states.tags[i.name] = i.tags,
            this._states.tags[i.name] = l.grep(this._states.tags[i.name], l.proxy(function(t, e) {
                return l.inArray(t, this._states.tags[i.name]) === e
            }, this)))
    }
    ,
    u.prototype.suppress = function(t) {
        l.each(t, l.proxy(function(t, e) {
            this._supress[e] = !0
        }, this))
    }
    ,
    u.prototype.release = function(t) {
        l.each(t, l.proxy(function(t, e) {
            delete this._supress[e]
        }, this))
    }
    ,
    u.prototype.pointer = function(t) {
        var e = {
            x: null,
            y: null
        };
        return (t = (t = t.originalEvent || t || i.event).touches && t.touches.length ? t.touches[0] : t.changedTouches && t.changedTouches.length ? t.changedTouches[0] : t).pageX ? (e.x = t.pageX,
        e.y = t.pageY) : (e.x = t.clientX,
        e.y = t.clientY),
        e
    }
    ,
    u.prototype.isNumeric = function(t) {
        return !isNaN(parseFloat(t))
    }
    ,
    u.prototype.difference = function(t, e) {
        return {
            x: t.x - e.x,
            y: t.y - e.y
        }
    }
    ,
    l.fn.owlCarousel = function(e) {
        var n = Array.prototype.slice.call(arguments, 1);
        return this.each(function() {
            var t = l(this)
              , i = t.data("owl.carousel");
            i || (i = new u(this,"object" == typeof e && e),
            t.data("owl.carousel", i),
            l.each(["next", "prev", "to", "destroy", "refresh", "replace", "add", "remove"], function(t, e) {
                i.register({
                    type: u.Type.Event,
                    name: e
                }),
                i.$element.on(e + ".owl.carousel.core", l.proxy(function(t) {
                    t.namespace && t.relatedTarget !== this && (this.suppress([e]),
                    i[e].apply(this, [].slice.call(arguments, 1)),
                    this.release([e]))
                }, i))
            })),
            "string" == typeof e && "_" !== e.charAt(0) && i[e].apply(i, n)
        })
    }
    ,
    l.fn.owlCarousel.Constructor = u
}(window.Zepto || window.jQuery, window, document),
function(e, i, t, n) {
    var r = function(t) {
        this._core = t,
        this._interval = null,
        this._visible = null,
        this._handlers = {
            "initialized.owl.carousel": e.proxy(function(t) {
                t.namespace && this._core.settings.autoRefresh && this.watch()
            }, this)
        },
        this._core.options = e.extend({}, r.Defaults, this._core.options),
        this._core.$element.on(this._handlers)
    };
    r.Defaults = {
        autoRefresh: !0,
        autoRefreshInterval: 500
    },
    r.prototype.watch = function() {
        this._interval || (this._visible = this._core.$element.is(":visible"),
        this._interval = i.setInterval(e.proxy(this.refresh, this), this._core.settings.autoRefreshInterval))
    }
    ,
    r.prototype.refresh = function() {
        this._core.$element.is(":visible") !== this._visible && (this._visible = !this._visible,
        this._core.$element.toggleClass("owl-hidden", !this._visible),
        this._visible && this._core.invalidate("width") && this._core.refresh())
    }
    ,
    r.prototype.destroy = function() {
        var t, e;
        for (t in i.clearInterval(this._interval),
        this._handlers)
            this._core.$element.off(t, this._handlers[t]);
        for (e in Object.getOwnPropertyNames(this))
            "function" != typeof this[e] && (this[e] = null)
    }
    ,
    e.fn.owlCarousel.Constructor.Plugins.AutoRefresh = r
}(window.Zepto || window.jQuery, window, document),
function(a, o, t, e) {
    var i = function(t) {
        this._core = t,
        this._loaded = [],
        this._handlers = {
            "initialized.owl.carousel change.owl.carousel resized.owl.carousel": a.proxy(function(t) {
                if (t.namespace && this._core.settings && this._core.settings.lazyLoad && (t.property && "position" == t.property.name || "initialized" == t.type))
                    for (var e = this._core.settings, i = e.center && Math.ceil(e.items / 2) || e.items, n = e.center && -1 * i || 0, r = (t.property && void 0 !== t.property.value ? t.property.value : this._core.current()) + n, o = this._core.clones().length, s = a.proxy(function(t, e) {
                        this.load(e)
                    }, this); n++ < i; )
                        this.load(o / 2 + this._core.relative(r)),
                        o && a.each(this._core.clones(this._core.relative(r)), s),
                        r++
            }, this)
        },
        this._core.options = a.extend({}, i.Defaults, this._core.options),
        this._core.$element.on(this._handlers)
    };
    i.Defaults = {
        lazyLoad: !1
    },
    i.prototype.load = function(t) {
        var e = this._core.$stage.children().eq(t)
          , i = e && e.find(".owl-lazy");
        !i || -1 < a.inArray(e.get(0), this._loaded) || (i.each(a.proxy(function(t, e) {
            var i, n = a(e), r = 1 < o.devicePixelRatio && n.attr("data-src-retina") || n.attr("data-src");
            this._core.trigger("load", {
                element: n,
                url: r
            }, "lazy"),
            n.is("img") ? n.one("load.owl.lazy", a.proxy(function() {
                n.css("opacity", 1),
                this._core.trigger("loaded", {
                    element: n,
                    url: r
                }, "lazy")
            }, this)).attr("src", r) : ((i = new Image).onload = a.proxy(function() {
                n.css({
                    "background-image": 'url("' + r + '")',
                    opacity: "1"
                }),
                this._core.trigger("loaded", {
                    element: n,
                    url: r
                }, "lazy")
            }, this),
            i.src = r)
        }, this)),
        this._loaded.push(e.get(0)))
    }
    ,
    i.prototype.destroy = function() {
        var t, e;
        for (t in this.handlers)
            this._core.$element.off(t, this.handlers[t]);
        for (e in Object.getOwnPropertyNames(this))
            "function" != typeof this[e] && (this[e] = null)
    }
    ,
    a.fn.owlCarousel.Constructor.Plugins.Lazy = i
}(window.Zepto || window.jQuery, window, document),
function(o, t, e, i) {
    var n = function(t) {
        this._core = t,
        this._handlers = {
            "initialized.owl.carousel refreshed.owl.carousel": o.proxy(function(t) {
                t.namespace && this._core.settings.autoHeight && this.update()
            }, this),
            "changed.owl.carousel": o.proxy(function(t) {
                t.namespace && this._core.settings.autoHeight && "position" == t.property.name && this.update()
            }, this),
            "loaded.owl.lazy": o.proxy(function(t) {
                t.namespace && this._core.settings.autoHeight && t.element.closest("." + this._core.settings.itemClass).index() === this._core.current() && this.update()
            }, this)
        },
        this._core.options = o.extend({}, n.Defaults, this._core.options),
        this._core.$element.on(this._handlers)
    };
    n.Defaults = {
        autoHeight: !1,
        autoHeightClass: "owl-height"
    },
    n.prototype.update = function() {
        var t, e = this._core._current, i = e + this._core.settings.items, n = this._core.$stage.children().toArray().slice(e, i), r = [];
        o.each(n, function(t, e) {
            r.push(o(e).height())
        }),
        t = Math.max.apply(null, r),
        this._core.$stage.parent().height(t).addClass(this._core.settings.autoHeightClass)
    }
    ,
    n.prototype.destroy = function() {
        var t, e;
        for (t in this._handlers)
            this._core.$element.off(t, this._handlers[t]);
        for (e in Object.getOwnPropertyNames(this))
            "function" != typeof this[e] && (this[e] = null)
    }
    ,
    o.fn.owlCarousel.Constructor.Plugins.AutoHeight = n
}(window.Zepto || window.jQuery, window, document),
function(c, t, e, i) {
    var n = function(t) {
        this._core = t,
        this._videos = {},
        this._playing = null,
        this._handlers = {
            "initialized.owl.carousel": c.proxy(function(t) {
                t.namespace && this._core.register({
                    type: "state",
                    name: "playing",
                    tags: ["interacting"]
                })
            }, this),
            "resize.owl.carousel": c.proxy(function(t) {
                t.namespace && this._core.settings.video && this.isInFullScreen() && t.preventDefault()
            }, this),
            "refreshed.owl.carousel": c.proxy(function(t) {
                t.namespace && this._core.is("resizing") && this._core.$stage.find(".cloned .owl-video-frame").remove()
            }, this),
            "changed.owl.carousel": c.proxy(function(t) {
                t.namespace && "position" === t.property.name && this._playing && this.stop()
            }, this),
            "prepared.owl.carousel": c.proxy(function(t) {
                if (t.namespace) {
                    var e = c(t.content).find(".owl-video");
                    e.length && (e.css("display", "none"),
                    this.fetch(e, c(t.content)))
                }
            }, this)
        },
        this._core.options = c.extend({}, n.Defaults, this._core.options),
        this._core.$element.on(this._handlers),
        this._core.$element.on("click.owl.video", ".owl-video-play-icon", c.proxy(function(t) {
            this.play(t)
        }, this))
    };
    n.Defaults = {
        video: !1,
        videoHeight: !1,
        videoWidth: !1
    },
    n.prototype.fetch = function(t, e) {
        var i = t.attr("data-vimeo-id") ? "vimeo" : t.attr("data-vzaar-id") ? "vzaar" : "youtube"
          , n = t.attr("data-vimeo-id") || t.attr("data-youtube-id") || t.attr("data-vzaar-id")
          , r = t.attr("data-width") || this._core.settings.videoWidth
          , o = t.attr("data-height") || this._core.settings.videoHeight
          , s = t.attr("href");
        if (!s)
            throw new Error("Missing video URL.");
        if (-1 < (n = s.match(/(http:|https:|)\/\/(player.|www.|app.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com)|vzaar\.com)\/(video\/|videos\/|embed\/|channels\/.+\/|groups\/.+\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/))[3].indexOf("youtu"))
            i = "youtube";
        else if (-1 < n[3].indexOf("vimeo"))
            i = "vimeo";
        else {
            if (!(-1 < n[3].indexOf("vzaar")))
                throw new Error("Video URL not supported.");
            i = "vzaar"
        }
        n = n[6],
        this._videos[s] = {
            type: i,
            id: n,
            width: r,
            height: o
        },
        e.attr("data-video", s),
        this.thumbnail(t, this._videos[s])
    }
    ,
    n.prototype.thumbnail = function(e, t) {
        var i, n, r = t.width && t.height ? 'style="width:' + t.width + "px;height:" + t.height + 'px;"' : "", o = e.find("img"), s = "src", a = "", l = this._core.settings, u = function(t) {
            '<div class="owl-video-play-icon"></div>',
            i = l.lazyLoad ? '<div class="owl-video-tn ' + a + '" ' + s + '="' + t + '"></div>' : '<div class="owl-video-tn" style="opacity:1;background-image:url(' + t + ')"></div>',
            e.after(i),
            e.after('<div class="owl-video-play-icon"></div>')
        };
        return e.wrap('<div class="owl-video-wrapper"' + r + "></div>"),
        this._core.settings.lazyLoad && (s = "data-src",
        a = "owl-lazy"),
        o.length ? (u(o.attr(s)),
        o.remove(),
        !1) : void ("youtube" === t.type ? (n = "//img.youtube.com/vi/" + t.id + "/hqdefault.jpg",
        u(n)) : "vimeo" === t.type ? c.ajax({
            type: "GET",
            url: "//vimeo.com/api/v2/video/" + t.id + ".json",
            jsonp: "callback",
            dataType: "jsonp",
            success: function(t) {
                n = t[0].thumbnail_large,
                u(n)
            }
        }) : "vzaar" === t.type && c.ajax({
            type: "GET",
            url: "//vzaar.com/api/videos/" + t.id + ".json",
            jsonp: "callback",
            dataType: "jsonp",
            success: function(t) {
                n = t.framegrab_url,
                u(n)
            }
        }))
    }
    ,
    n.prototype.stop = function() {
        this._core.trigger("stop", null, "video"),
        this._playing.find(".owl-video-frame").remove(),
        this._playing.removeClass("owl-video-playing"),
        this._playing = null,
        this._core.leave("playing"),
        this._core.trigger("stopped", null, "video")
    }
    ,
    n.prototype.play = function(t) {
        var e, i = c(t.target).closest("." + this._core.settings.itemClass), n = this._videos[i.attr("data-video")], r = n.width || "100%", o = n.height || this._core.$stage.height();
        this._playing || (this._core.enter("playing"),
        this._core.trigger("play", null, "video"),
        i = this._core.items(this._core.relative(i.index())),
        this._core.reset(i.index()),
        "youtube" === n.type ? e = '<iframe width="' + r + '" height="' + o + '" src="//www.youtube.com/embed/' + n.id + "?autoplay=1&rel=0&v=" + n.id + '" frameborder="0" allowfullscreen></iframe>' : "vimeo" === n.type ? e = '<iframe src="//player.vimeo.com/video/' + n.id + '?autoplay=1" width="' + r + '" height="' + o + '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>' : "vzaar" === n.type && (e = '<iframe frameborder="0"height="' + o + '"width="' + r + '" allowfullscreen mozallowfullscreen webkitAllowFullScreen src="//view.vzaar.com/' + n.id + '/player?autoplay=true"></iframe>'),
        c('<div class="owl-video-frame">' + e + "</div>").insertAfter(i.find(".owl-video")),
        this._playing = i.addClass("owl-video-playing"))
    }
    ,
    n.prototype.isInFullScreen = function() {
        var t = e.fullscreenElement || e.mozFullScreenElement || e.webkitFullscreenElement;
        return t && c(t).parent().hasClass("owl-video-frame")
    }
    ,
    n.prototype.destroy = function() {
        var t, e;
        for (t in this._core.$element.off("click.owl.video"),
        this._handlers)
            this._core.$element.off(t, this._handlers[t]);
        for (e in Object.getOwnPropertyNames(this))
            "function" != typeof this[e] && (this[e] = null)
    }
    ,
    c.fn.owlCarousel.Constructor.Plugins.Video = n
}(window.Zepto || window.jQuery, window, document),
function(s, t, e, i) {
    var n = function(t) {
        this.core = t,
        this.core.options = s.extend({}, n.Defaults, this.core.options),
        this.swapping = !0,
        this.previous = void 0,
        this.next = void 0,
        this.handlers = {
            "change.owl.carousel": s.proxy(function(t) {
                t.namespace && "position" == t.property.name && (this.previous = this.core.current(),
                this.next = t.property.value)
            }, this),
            "drag.owl.carousel dragged.owl.carousel translated.owl.carousel": s.proxy(function(t) {
                t.namespace && (this.swapping = "translated" == t.type)
            }, this),
            "translate.owl.carousel": s.proxy(function(t) {
                t.namespace && this.swapping && (this.core.options.animateOut || this.core.options.animateIn) && this.swap()
            }, this)
        },
        this.core.$element.on(this.handlers)
    };
    n.Defaults = {
        animateOut: !1,
        animateIn: !1
    },
    n.prototype.swap = function() {
        if (1 === this.core.settings.items && s.support.animation && s.support.transition) {
            this.core.speed(0);
            var t, e = s.proxy(this.clear, this), i = this.core.$stage.children().eq(this.previous), n = this.core.$stage.children().eq(this.next), r = this.core.settings.animateIn, o = this.core.settings.animateOut;
            this.core.current() !== this.previous && (o && (t = this.core.coordinates(this.previous) - this.core.coordinates(this.next),
            i.one(s.support.animation.end, e).css({
                left: t + "px"
            }).addClass("animated owl-animated-out").addClass(o)),
            r && n.one(s.support.animation.end, e).addClass("animated owl-animated-in").addClass(r))
        }
    }
    ,
    n.prototype.clear = function(t) {
        s(t.target).css({
            left: ""
        }).removeClass("animated owl-animated-out owl-animated-in").removeClass(this.core.settings.animateIn).removeClass(this.core.settings.animateOut),
        this.core.onTransitionEnd()
    }
    ,
    n.prototype.destroy = function() {
        var t, e;
        for (t in this.handlers)
            this.core.$element.off(t, this.handlers[t]);
        for (e in Object.getOwnPropertyNames(this))
            "function" != typeof this[e] && (this[e] = null)
    }
    ,
    s.fn.owlCarousel.Constructor.Plugins.Animate = n
}(window.Zepto || window.jQuery, window, document),
function(n, r, e, t) {
    var i = function(t) {
        this._core = t,
        this._call = null,
        this._time = 0,
        this._timeout = 0,
        this._paused = !0,
        this._handlers = {
            "changed.owl.carousel": n.proxy(function(t) {
                t.namespace && "settings" === t.property.name ? this._core.settings.autoplay ? this.play() : this.stop() : t.namespace && "position" === t.property.name && this._paused && (this._time = 0)
            }, this),
            "initialized.owl.carousel": n.proxy(function(t) {
                t.namespace && this._core.settings.autoplay && this.play()
            }, this),
            "play.owl.autoplay": n.proxy(function(t, e, i) {
                t.namespace && this.play(e, i)
            }, this),
            "stop.owl.autoplay": n.proxy(function(t) {
                t.namespace && this.stop()
            }, this),
            "mouseover.owl.autoplay": n.proxy(function() {
                this._core.settings.autoplayHoverPause && this._core.is("rotating") && this.pause()
            }, this),
            "mouseleave.owl.autoplay": n.proxy(function() {
                this._core.settings.autoplayHoverPause && this._core.is("rotating") && this.play()
            }, this),
            "touchstart.owl.core": n.proxy(function() {
                this._core.settings.autoplayHoverPause && this._core.is("rotating") && this.pause()
            }, this),
            "touchend.owl.core": n.proxy(function() {
                this._core.settings.autoplayHoverPause && this.play()
            }, this)
        },
        this._core.$element.on(this._handlers),
        this._core.options = n.extend({}, i.Defaults, this._core.options)
    };
    i.Defaults = {
        autoplay: !1,
        autoplayTimeout: 5e3,
        autoplayHoverPause: !1,
        autoplaySpeed: !1
    },
    i.prototype._next = function(t) {
        this._call = r.setTimeout(n.proxy(this._next, this, t), this._timeout * (Math.round(this.read() / this._timeout) + 1) - this.read()),
        this._core.is("busy") || this._core.is("interacting") || e.hidden || this._core.next(t || this._core.settings.autoplaySpeed)
    }
    ,
    i.prototype.read = function() {
        return (new Date).getTime() - this._time
    }
    ,
    i.prototype.play = function(t, e) {
        var i;
        this._core.is("rotating") || this._core.enter("rotating"),
        t = t || this._core.settings.autoplayTimeout,
        i = Math.min(this._time % (this._timeout || t), t),
        this._paused ? (this._time = this.read(),
        this._paused = !1) : r.clearTimeout(this._call),
        this._time += this.read() % t - i,
        this._timeout = t,
        this._call = r.setTimeout(n.proxy(this._next, this, e), t - i)
    }
    ,
    i.prototype.stop = function() {
        this._core.is("rotating") && (this._time = 0,
        this._paused = !0,
        r.clearTimeout(this._call),
        this._core.leave("rotating"))
    }
    ,
    i.prototype.pause = function() {
        this._core.is("rotating") && !this._paused && (this._time = this.read(),
        this._paused = !0,
        r.clearTimeout(this._call))
    }
    ,
    i.prototype.destroy = function() {
        var t, e;
        for (t in this.stop(),
        this._handlers)
            this._core.$element.off(t, this._handlers[t]);
        for (e in Object.getOwnPropertyNames(this))
            "function" != typeof this[e] && (this[e] = null)
    }
    ,
    n.fn.owlCarousel.Constructor.Plugins.autoplay = i
}(window.Zepto || window.jQuery, window, document),
function(o, t, e, i) {
    "use strict";
    var n = function(t) {
        this._core = t,
        this._initialized = !1,
        this._pages = [],
        this._controls = {},
        this._templates = [],
        this.$element = this._core.$element,
        this._overrides = {
            next: this._core.next,
            prev: this._core.prev,
            to: this._core.to
        },
        this._handlers = {
            "prepared.owl.carousel": o.proxy(function(t) {
                t.namespace && this._core.settings.dotsData && this._templates.push('<div class="' + this._core.settings.dotClass + '">' + o(t.content).find("[data-dot]").addBack("[data-dot]").attr("data-dot") + "</div>")
            }, this),
            "added.owl.carousel": o.proxy(function(t) {
                t.namespace && this._core.settings.dotsData && this._templates.splice(t.position, 0, this._templates.pop())
            }, this),
            "remove.owl.carousel": o.proxy(function(t) {
                t.namespace && this._core.settings.dotsData && this._templates.splice(t.position, 1)
            }, this),
            "changed.owl.carousel": o.proxy(function(t) {
                t.namespace && "position" == t.property.name && this.draw()
            }, this),
            "initialized.owl.carousel": o.proxy(function(t) {
                t.namespace && !this._initialized && (this._core.trigger("initialize", null, "navigation"),
                this.initialize(),
                this.update(),
                this.draw(),
                this._initialized = !0,
                this._core.trigger("initialized", null, "navigation"))
            }, this),
            "refreshed.owl.carousel": o.proxy(function(t) {
                t.namespace && this._initialized && (this._core.trigger("refresh", null, "navigation"),
                this.update(),
                this.draw(),
                this._core.trigger("refreshed", null, "navigation"))
            }, this)
        },
        this._core.options = o.extend({}, n.Defaults, this._core.options),
        this.$element.on(this._handlers)
    };
    n.Defaults = {
        nav: !1,
        navText: ['<span aria-label="prev">&#x2039;</span>', '<span aria-label="next">&#x203a;</span>'],
        navSpeed: !1,
        navElement: 'button role="presentation"',
        navContainer: !1,
        navContainerClass: "owl-nav",
        navClass: ["owl-prev", "owl-next"],
        slideBy: 1,
        dotClass: "owl-dot",
        dotsClass: "owl-dots",
        dots: !0,
        dotsEach: !1,
        dotsData: !1,
        dotsSpeed: !1,
        dotsContainer: !1
    },
    n.prototype.initialize = function() {
        var t, i = this._core.settings;
        for (t in this._controls.$relative = (i.navContainer ? o(i.navContainer) : o("<div>").addClass(i.navContainerClass).appendTo(this.$element)).addClass("disabled"),
        this._controls.$previous = o("<" + i.navElement + ">").addClass(i.navClass[0]).html(i.navText[0]).prependTo(this._controls.$relative).on("click", o.proxy(function(t) {
            this.prev(i.navSpeed)
        }, this)),
        this._controls.$next = o("<" + i.navElement + ">").addClass(i.navClass[1]).html(i.navText[1]).appendTo(this._controls.$relative).on("click", o.proxy(function(t) {
            this.next(i.navSpeed)
        }, this)),
        i.dotsData || (this._templates = [o("<button>").addClass(i.dotClass).append(o("<span>")).prop("outerHTML")]),
        this._controls.$absolute = (i.dotsContainer ? o(i.dotsContainer) : o("<div>").addClass(i.dotsClass).appendTo(this.$element)).addClass("disabled"),
        this._controls.$absolute.on("click", "button", o.proxy(function(t) {
            var e = o(t.target).parent().is(this._controls.$absolute) ? o(t.target).index() : o(t.target).parent().index();
            t.preventDefault(),
            this.to(e, i.dotsSpeed)
        }, this)),
        this._overrides)
            this._core[t] = o.proxy(this[t], this)
    }
    ,
    n.prototype.destroy = function() {
        var t, e, i, n;
        for (t in this._handlers)
            this.$element.off(t, this._handlers[t]);
        for (e in this._controls)
            "$relative" === e && settings.navContainer ? this._controls[e].html("") : this._controls[e].remove();
        for (n in this.overides)
            this._core[n] = this._overrides[n];
        for (i in Object.getOwnPropertyNames(this))
            "function" != typeof this[i] && (this[i] = null)
    }
    ,
    n.prototype.update = function() {
        var t, e, i = this._core.clones().length / 2, n = i + this._core.items().length, r = this._core.maximum(!0), o = this._core.settings, s = o.center || o.autoWidth || o.dotsData ? 1 : o.dotsEach || o.items;
        if ("page" !== o.slideBy && (o.slideBy = Math.min(o.slideBy, o.items)),
        o.dots || "page" == o.slideBy)
            for (this._pages = [],
            t = i,
            e = 0; t < n; t++) {
                if (s <= e || 0 === e) {
                    if (this._pages.push({
                        start: Math.min(r, t - i),
                        end: t - i + s - 1
                    }),
                    Math.min(r, t - i) === r)
                        break;
                    e = 0,
                    0
                }
                e += this._core.mergers(this._core.relative(t))
            }
    }
    ,
    n.prototype.draw = function() {
        var t, e = this._core.settings, i = this._core.items().length <= e.items, n = this._core.relative(this._core.current()), r = e.loop || e.rewind;
        this._controls.$relative.toggleClass("disabled", !e.nav || i),
        e.nav && (this._controls.$previous.toggleClass("disabled", !r && n <= this._core.minimum(!0)),
        this._controls.$next.toggleClass("disabled", !r && n >= this._core.maximum(!0))),
        this._controls.$absolute.toggleClass("disabled", !e.dots || i),
        e.dots && (t = this._pages.length - this._controls.$absolute.children().length,
        e.dotsData && 0 !== t ? this._controls.$absolute.html(this._templates.join("")) : 0 < t ? this._controls.$absolute.append(new Array(t + 1).join(this._templates[0])) : t < 0 && this._controls.$absolute.children().slice(t).remove(),
        this._controls.$absolute.find(".active").removeClass("active"),
        this._controls.$absolute.children().eq(o.inArray(this.current(), this._pages)).addClass("active"))
    }
    ,
    n.prototype.onTrigger = function(t) {
        var e = this._core.settings;
        t.page = {
            index: o.inArray(this.current(), this._pages),
            count: this._pages.length,
            size: e && (e.center || e.autoWidth || e.dotsData ? 1 : e.dotsEach || e.items)
        }
    }
    ,
    n.prototype.current = function() {
        var i = this._core.relative(this._core.current());
        return o.grep(this._pages, o.proxy(function(t, e) {
            return t.start <= i && t.end >= i
        }, this)).pop()
    }
    ,
    n.prototype.getPosition = function(t) {
        var e, i, n = this._core.settings;
        return "page" == n.slideBy ? (e = o.inArray(this.current(), this._pages),
        i = this._pages.length,
        t ? ++e : --e,
        e = this._pages[(e % i + i) % i].start) : (e = this._core.relative(this._core.current()),
        i = this._core.items().length,
        t ? e += n.slideBy : e -= n.slideBy),
        e
    }
    ,
    n.prototype.next = function(t) {
        o.proxy(this._overrides.to, this._core)(this.getPosition(!0), t)
    }
    ,
    n.prototype.prev = function(t) {
        o.proxy(this._overrides.to, this._core)(this.getPosition(!1), t)
    }
    ,
    n.prototype.to = function(t, e, i) {
        var n;
        !i && this._pages.length ? (n = this._pages.length,
        o.proxy(this._overrides.to, this._core)(this._pages[(t % n + n) % n].start, e)) : o.proxy(this._overrides.to, this._core)(t, e)
    }
    ,
    o.fn.owlCarousel.Constructor.Plugins.Navigation = n
}(window.Zepto || window.jQuery, window, document),
function(n, r, t, e) {
    "use strict";
    var i = function(t) {
        this._core = t,
        this._hashes = {},
        this.$element = this._core.$element,
        this._handlers = {
            "initialized.owl.carousel": n.proxy(function(t) {
                t.namespace && "URLHash" === this._core.settings.startPosition && n(r).trigger("hashchange.owl.navigation")
            }, this),
            "prepared.owl.carousel": n.proxy(function(t) {
                if (t.namespace) {
                    var e = n(t.content).find("[data-hash]").addBack("[data-hash]").attr("data-hash");
                    if (!e)
                        return;
                    this._hashes[e] = t.content
                }
            }, this),
            "changed.owl.carousel": n.proxy(function(t) {
                if (t.namespace && "position" === t.property.name) {
                    var i = this._core.items(this._core.relative(this._core.current()))
                      , e = n.map(this._hashes, function(t, e) {
                        return t === i ? e : null
                    }).join();
                    if (!e || r.location.hash.slice(1) === e)
                        return;
                    r.location.hash = e
                }
            }, this)
        },
        this._core.options = n.extend({}, i.Defaults, this._core.options),
        this.$element.on(this._handlers),
        n(r).on("hashchange.owl.navigation", n.proxy(function(t) {
            var e = r.location.hash.substring(1)
              , i = this._core.$stage.children()
              , n = this._hashes[e] && i.index(this._hashes[e]);
            void 0 !== n && n !== this._core.current() && this._core.to(this._core.relative(n), !1, !0)
        }, this))
    };
    i.Defaults = {
        URLhashListener: !1
    },
    i.prototype.destroy = function() {
        var t, e;
        for (t in n(r).off("hashchange.owl.navigation"),
        this._handlers)
            this._core.$element.off(t, this._handlers[t]);
        for (e in Object.getOwnPropertyNames(this))
            "function" != typeof this[e] && (this[e] = null)
    }
    ,
    n.fn.owlCarousel.Constructor.Plugins.Hash = i
}(window.Zepto || window.jQuery, window, document),
function(r, t, e, o) {
    function i(t, i) {
        var n = !1
          , e = t.charAt(0).toUpperCase() + t.slice(1);
        return r.each((t + " " + a.join(e + " ") + e).split(" "), function(t, e) {
            return s[e] !== o ? (n = !i || e,
            !1) : void 0
        }),
        n
    }
    function n(t) {
        return i(t, !0)
    }
    var s = r("<support>").get(0).style
      , a = "Webkit Moz O ms".split(" ")
      , l = {
        transition: {
            end: {
                WebkitTransition: "webkitTransitionEnd",
                MozTransition: "transitionend",
                OTransition: "oTransitionEnd",
                transition: "transitionend"
            }
        },
        animation: {
            end: {
                WebkitAnimation: "webkitAnimationEnd",
                MozAnimation: "animationend",
                OAnimation: "oAnimationEnd",
                animation: "animationend"
            }
        }
    }
      , u = function() {
        return !!i("transform")
    }
      , c = function() {
        return !!i("perspective")
    }
      , h = function() {
        return !!i("animation")
    };
    (function() {
        return !!i("transition")
    }
    )() && (r.support.transition = new String(n("transition")),
    r.support.transition.end = l.transition.end[r.support.transition]),
    h() && (r.support.animation = new String(n("animation")),
    r.support.animation.end = l.animation.end[r.support.animation]),
    u() && (r.support.transform = new String(n("transform")),
    r.support.transform3d = c())
}(window.Zepto || window.jQuery, window, document),
function(t, e) {
    "function" == typeof define && define.amd ? define(["jquery"], function(t) {
        return e(t)
    }) : "object" == typeof exports ? module.exports = e(require("jquery")) : e(jQuery)
}(0, function(S) {
    function e(t) {
        this.$container,
        this.constraints = null,
        this.__$tooltip,
        this.__init(t)
    }
    function o(i, n) {
        var r = !0;
        return S.each(i, function(t, e) {
            return void 0 === n[t] || i[t] !== n[t] ? r = !1 : void 0
        }),
        r
    }
    function c(t) {
        var e = t.attr("id")
          , i = e ? $.window.document.getElementById(e) : null;
        return i ? i === t[0] : S.contains($.window.document.body, t[0])
    }
    var h = {
        animation: "fade",
        animationDuration: 350,
        content: null,
        contentAsHTML: !1,
        contentCloning: !1,
        debug: !0,
        delay: 300,
        delayTouch: [300, 500],
        functionInit: null,
        functionBefore: null,
        functionReady: null,
        functionAfter: null,
        functionFormat: null,
        IEmin: 6,
        interactive: !1,
        multiple: !1,
        parent: null,
        plugins: ["sideTip"],
        repositionOnScroll: !1,
        restoration: "none",
        selfDestruction: !0,
        theme: [],
        timer: 0,
        trackerInterval: 500,
        trackOrigin: !1,
        trackTooltip: !1,
        trigger: "hover",
        triggerClose: {
            click: !1,
            mouseleave: !1,
            originClick: !1,
            scroll: !1,
            tap: !1,
            touchleave: !1
        },
        triggerOpen: {
            click: !1,
            mouseenter: !1,
            tap: !1,
            touchstart: !1
        },
        updateAnimation: "rotate",
        zIndex: 9999999
    }
      , r = "undefined" != typeof window ? window : null
      , $ = {
        hasTouchCapability: !(!r || !("ontouchstart"in r || r.DocumentTouch && r.document instanceof r.DocumentTouch || r.navigator.maxTouchPoints)),
        hasTransitions: function() {
            if (!r)
                return !1;
            var t = (r.document.body || r.document.documentElement).style
              , e = "transition"
              , i = ["Moz", "Webkit", "Khtml", "O", "ms"];
            if ("string" == typeof t[e])
                return !0;
            e = e.charAt(0).toUpperCase() + e.substr(1);
            for (var n = 0; n < i.length; n++)
                if ("string" == typeof t[i[n] + e])
                    return !0;
            return !1
        }(),
        IE: !1,
        semVer: "4.2.6",
        window: r
    }
      , t = function() {
        this.__$emitterPrivate = S({}),
        this.__$emitterPublic = S({}),
        this.__instancesLatestArr = [],
        this.__plugins = {},
        this._env = $
    };
    t.prototype = {
        __bridge: function(t, i, n) {
            if (!i[n]) {
                var e = function() {};
                e.prototype = t;
                var r = new e;
                r.__init && r.__init(i),
                S.each(t, function(t, e) {
                    0 != t.indexOf("__") && (i[t] ? h.debug && console.log("The " + t + " method of the " + n + " plugin conflicts with another plugin or native methods") : (i[t] = function() {
                        return r[t].apply(r, Array.prototype.slice.apply(arguments))
                    }
                    ,
                    i[t].bridged = r))
                }),
                i[n] = r
            }
            return this
        },
        __setWindow: function(t) {
            return $.window = t,
            this
        },
        _getRuler: function(t) {
            return new e(t)
        },
        _off: function() {
            return this.__$emitterPrivate.off.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments)),
            this
        },
        _on: function() {
            return this.__$emitterPrivate.on.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments)),
            this
        },
        _one: function() {
            return this.__$emitterPrivate.one.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments)),
            this
        },
        _plugin: function(t) {
            if ("string" == typeof t) {
                var i = t
                  , n = null;
                return 0 < i.indexOf(".") ? n = this.__plugins[i] : S.each(this.__plugins, function(t, e) {
                    return e.name.substring(e.name.length - i.length - 1) == "." + i ? (n = e,
                    !1) : void 0
                }),
                n
            }
            if (t.name.indexOf(".") < 0)
                throw new Error("Plugins must be namespaced");
            return (this.__plugins[t.name] = t).core && this.__bridge(t.core, this, t.name),
            this
        },
        _trigger: function() {
            var t = Array.prototype.slice.apply(arguments);
            return "string" == typeof t[0] && (t[0] = {
                type: t[0]
            }),
            this.__$emitterPrivate.trigger.apply(this.__$emitterPrivate, t),
            this.__$emitterPublic.trigger.apply(this.__$emitterPublic, t),
            this
        },
        instances: function(t) {
            var n = [];
            return S(t || ".tooltipstered").each(function() {
                var i = S(this)
                  , t = i.data("tooltipster-ns");
                t && S.each(t, function(t, e) {
                    n.push(i.data(e))
                })
            }),
            n
        },
        instancesLatest: function() {
            return this.__instancesLatestArr
        },
        off: function() {
            return this.__$emitterPublic.off.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments)),
            this
        },
        on: function() {
            return this.__$emitterPublic.on.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments)),
            this
        },
        one: function() {
            return this.__$emitterPublic.one.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments)),
            this
        },
        origins: function(t) {
            return S((t ? t + " " : "") + ".tooltipstered").toArray()
        },
        setDefaults: function(t) {
            return S.extend(h, t),
            this
        },
        triggerHandler: function() {
            return this.__$emitterPublic.triggerHandler.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments)),
            this
        }
    },
    S.tooltipster = new t,
    S.Tooltipster = function(t, e) {
        this.__callbacks = {
            close: [],
            open: []
        },
        this.__closingTime,
        this.__Content,
        this.__contentBcr,
        this.__destroyed = !1,
        this.__$emitterPrivate = S({}),
        this.__$emitterPublic = S({}),
        this.__enabled = !0,
        this.__garbageCollector,
        this.__Geometry,
        this.__lastPosition,
        this.__namespace = "tooltipster-" + Math.round(1e6 * Math.random()),
        this.__options,
        this.__$originParents,
        this.__pointerIsOverOrigin = !1,
        this.__previousThemes = [],
        this.__state = "closed",
        this.__timeouts = {
            close: [],
            open: null
        },
        this.__touchEvents = [],
        this.__tracker = null,
        this._$origin,
        this._$tooltip,
        this.__init(t, e)
    }
    ,
    S.Tooltipster.prototype = {
        __init: function(t, e) {
            var i = this;
            if (i._$origin = S(t),
            i.__options = S.extend(!0, {}, h, e),
            i.__optionsFormat(),
            !$.IE || $.IE >= i.__options.IEmin) {
                var n = null;
                if (void 0 === i._$origin.data("tooltipster-initialTitle") && (void 0 === (n = i._$origin.attr("title")) && (n = null),
                i._$origin.data("tooltipster-initialTitle", n)),
                null !== i.__options.content)
                    i.__contentSet(i.__options.content);
                else {
                    var r, o = i._$origin.attr("data-tooltip-content");
                    o && (r = S(o)),
                    r && r[0] ? i.__contentSet(r.first()) : i.__contentSet(n)
                }
                i._$origin.removeAttr("title").addClass("tooltipstered"),
                i.__prepareOrigin(),
                i.__prepareGC(),
                S.each(i.__options.plugins, function(t, e) {
                    i._plug(e)
                }),
                $.hasTouchCapability && S($.window.document.body).on("touchmove." + i.__namespace + "-triggerOpen", function(t) {
                    i._touchRecordEvent(t)
                }),
                i._on("created", function() {
                    i.__prepareTooltip()
                })._on("repositioned", function(t) {
                    i.__lastPosition = t.position
                })
            } else
                i.__options.disabled = !0
        },
        __contentInsert: function() {
            var t = this
              , e = t._$tooltip.find(".tooltipster-content")
              , i = t.__Content;
            return t._trigger({
                type: "format",
                content: t.__Content,
                format: function(t) {
                    i = t
                }
            }),
            t.__options.functionFormat && (i = t.__options.functionFormat.call(t, t, {
                origin: t._$origin[0]
            }, t.__Content)),
            "string" != typeof i || t.__options.contentAsHTML ? e.empty().append(i) : e.text(i),
            t
        },
        __contentSet: function(t) {
            return t instanceof S && this.__options.contentCloning && (t = t.clone(!0)),
            this.__Content = t,
            this._trigger({
                type: "updated",
                content: t
            }),
            this
        },
        __destroyError: function() {
            throw new Error("This tooltip has been destroyed and cannot execute your method call.")
        },
        __geometry: function() {
            var t = this._$origin
              , e = this._$origin.is("area");
            if (e) {
                var i = this._$origin.parent().attr("name");
                t = S('img[usemap="#' + i + '"]')
            }
            var n = t[0].getBoundingClientRect()
              , r = S($.window.document)
              , o = S($.window)
              , s = t
              , a = {
                available: {
                    document: null,
                    window: null
                },
                document: {
                    size: {
                        height: r.height(),
                        width: r.width()
                    }
                },
                window: {
                    scroll: {
                        left: $.window.scrollX || $.window.document.documentElement.scrollLeft,
                        top: $.window.scrollY || $.window.document.documentElement.scrollTop
                    },
                    size: {
                        height: o.height(),
                        width: o.width()
                    }
                },
                origin: {
                    fixedLineage: !1,
                    offset: {},
                    size: {
                        height: n.bottom - n.top,
                        width: n.right - n.left
                    },
                    usemapImage: e ? t[0] : null,
                    windowOffset: {
                        bottom: n.bottom,
                        left: n.left,
                        right: n.right,
                        top: n.top
                    }
                }
            };
            if (e) {
                var l = this._$origin.attr("shape")
                  , u = this._$origin.attr("coords");
                if (u && (u = u.split(","),
                S.map(u, function(t, e) {
                    u[e] = parseInt(t)
                })),
                "default" != l)
                    switch (l) {
                    case "circle":
                        var c = u[0]
                          , h = u[1]
                          , f = u[2]
                          , d = h - f
                          , p = c - f;
                        a.origin.size.height = 2 * f,
                        a.origin.size.width = a.origin.size.height,
                        a.origin.windowOffset.left += p,
                        a.origin.windowOffset.top += d;
                        break;
                    case "rect":
                        var g = u[0]
                          , _ = u[1]
                          , m = u[2]
                          , v = u[3];
                        a.origin.size.height = v - _,
                        a.origin.size.width = m - g,
                        a.origin.windowOffset.left += g,
                        a.origin.windowOffset.top += _;
                        break;
                    case "poly":
                        for (var y = 0, w = 0, b = 0, x = 0, T = "even", C = 0; C < u.length; C++) {
                            var k = u[C];
                            T = "even" == T ? (b < k && (b = k,
                            0 === C && (y = b)),
                            k < y && (y = k),
                            "odd") : (x < k && (x = k,
                            1 == C && (w = x)),
                            k < w && (w = k),
                            "even")
                        }
                        a.origin.size.height = x - w,
                        a.origin.size.width = b - y,
                        a.origin.windowOffset.left += y,
                        a.origin.windowOffset.top += w
                    }
            }
            for (this._trigger({
                type: "geometry",
                edit: function(t) {
                    a.origin.size.height = t.height,
                    a.origin.windowOffset.left = t.left,
                    a.origin.windowOffset.top = t.top,
                    a.origin.size.width = t.width
                },
                geometry: {
                    height: a.origin.size.height,
                    left: a.origin.windowOffset.left,
                    top: a.origin.windowOffset.top,
                    width: a.origin.size.width
                }
            }),
            a.origin.windowOffset.right = a.origin.windowOffset.left + a.origin.size.width,
            a.origin.windowOffset.bottom = a.origin.windowOffset.top + a.origin.size.height,
            a.origin.offset.left = a.origin.windowOffset.left + a.window.scroll.left,
            a.origin.offset.top = a.origin.windowOffset.top + a.window.scroll.top,
            a.origin.offset.bottom = a.origin.offset.top + a.origin.size.height,
            a.origin.offset.right = a.origin.offset.left + a.origin.size.width,
            a.available.document = {
                bottom: {
                    height: a.document.size.height - a.origin.offset.bottom,
                    width: a.document.size.width
                },
                left: {
                    height: a.document.size.height,
                    width: a.origin.offset.left
                },
                right: {
                    height: a.document.size.height,
                    width: a.document.size.width - a.origin.offset.right
                },
                top: {
                    height: a.origin.offset.top,
                    width: a.document.size.width
                }
            },
            a.available.window = {
                bottom: {
                    height: Math.max(a.window.size.height - Math.max(a.origin.windowOffset.bottom, 0), 0),
                    width: a.window.size.width
                },
                left: {
                    height: a.window.size.height,
                    width: Math.max(a.origin.windowOffset.left, 0)
                },
                right: {
                    height: a.window.size.height,
                    width: Math.max(a.window.size.width - Math.max(a.origin.windowOffset.right, 0), 0)
                },
                top: {
                    height: Math.max(a.origin.windowOffset.top, 0),
                    width: a.window.size.width
                }
            }; "html" != s[0].tagName.toLowerCase(); ) {
                if ("fixed" == s.css("position")) {
                    a.origin.fixedLineage = !0;
                    break
                }
                s = s.parent()
            }
            return a
        },
        __optionsFormat: function() {
            return "number" == typeof this.__options.animationDuration && (this.__options.animationDuration = [this.__options.animationDuration, this.__options.animationDuration]),
            "number" == typeof this.__options.delay && (this.__options.delay = [this.__options.delay, this.__options.delay]),
            "number" == typeof this.__options.delayTouch && (this.__options.delayTouch = [this.__options.delayTouch, this.__options.delayTouch]),
            "string" == typeof this.__options.theme && (this.__options.theme = [this.__options.theme]),
            null === this.__options.parent ? this.__options.parent = S($.window.document.body) : "string" == typeof this.__options.parent && (this.__options.parent = S(this.__options.parent)),
            "hover" == this.__options.trigger ? (this.__options.triggerOpen = {
                mouseenter: !0,
                touchstart: !0
            },
            this.__options.triggerClose = {
                mouseleave: !0,
                originClick: !0,
                touchleave: !0
            }) : "click" == this.__options.trigger && (this.__options.triggerOpen = {
                click: !0,
                tap: !0
            },
            this.__options.triggerClose = {
                click: !0,
                tap: !0
            }),
            this._trigger("options"),
            this
        },
        __prepareGC: function() {
            var t = this;
            return t.__options.selfDestruction ? t.__garbageCollector = setInterval(function() {
                var i = (new Date).getTime();
                t.__touchEvents = S.grep(t.__touchEvents, function(t, e) {
                    return 6e4 < i - t.time
                }),
                c(t._$origin) || t.close(function() {
                    t.destroy()
                })
            }, 2e4) : clearInterval(t.__garbageCollector),
            t
        },
        __prepareOrigin: function() {
            var e = this;
            if (e._$origin.off("." + e.__namespace + "-triggerOpen"),
            $.hasTouchCapability && e._$origin.on("touchstart." + e.__namespace + "-triggerOpen touchend." + e.__namespace + "-triggerOpen touchcancel." + e.__namespace + "-triggerOpen", function(t) {
                e._touchRecordEvent(t)
            }),
            e.__options.triggerOpen.click || e.__options.triggerOpen.tap && $.hasTouchCapability) {
                var t = "";
                e.__options.triggerOpen.click && (t += "click." + e.__namespace + "-triggerOpen "),
                e.__options.triggerOpen.tap && $.hasTouchCapability && (t += "touchend." + e.__namespace + "-triggerOpen"),
                e._$origin.on(t, function(t) {
                    e._touchIsMeaningfulEvent(t) && e._open(t)
                })
            }
            if (e.__options.triggerOpen.mouseenter || e.__options.triggerOpen.touchstart && $.hasTouchCapability) {
                t = "";
                e.__options.triggerOpen.mouseenter && (t += "mouseenter." + e.__namespace + "-triggerOpen "),
                e.__options.triggerOpen.touchstart && $.hasTouchCapability && (t += "touchstart." + e.__namespace + "-triggerOpen"),
                e._$origin.on(t, function(t) {
                    !e._touchIsTouchEvent(t) && e._touchIsEmulatedEvent(t) || (e.__pointerIsOverOrigin = !0,
                    e._openShortly(t))
                })
            }
            if (e.__options.triggerClose.mouseleave || e.__options.triggerClose.touchleave && $.hasTouchCapability) {
                t = "";
                e.__options.triggerClose.mouseleave && (t += "mouseleave." + e.__namespace + "-triggerOpen "),
                e.__options.triggerClose.touchleave && $.hasTouchCapability && (t += "touchend." + e.__namespace + "-triggerOpen touchcancel." + e.__namespace + "-triggerOpen"),
                e._$origin.on(t, function(t) {
                    e._touchIsMeaningfulEvent(t) && (e.__pointerIsOverOrigin = !1)
                })
            }
            return e
        },
        __prepareTooltip: function() {
            var i = this
              , t = i.__options.interactive ? "auto" : "";
            return i._$tooltip.attr("id", i.__namespace).css({
                "pointer-events": t,
                zIndex: i.__options.zIndex
            }),
            S.each(i.__previousThemes, function(t, e) {
                i._$tooltip.removeClass(e)
            }),
            S.each(i.__options.theme, function(t, e) {
                i._$tooltip.addClass(e)
            }),
            i.__previousThemes = S.merge([], i.__options.theme),
            i
        },
        __scrollHandler: function(t) {
            var e = this;
            if (e.__options.triggerClose.scroll)
                e._close(t);
            else if (c(e._$origin) && c(e._$tooltip)) {
                var s = null;
                if (t.target === $.window.document)
                    e.__Geometry.origin.fixedLineage || e.__options.repositionOnScroll && e.reposition(t);
                else {
                    s = e.__geometry();
                    var a = !1;
                    if ("fixed" != e._$origin.css("position") && e.__$originParents.each(function(t, e) {
                        var i = S(e)
                          , n = i.css("overflow-x")
                          , r = i.css("overflow-y");
                        if ("visible" != n || "visible" != r) {
                            var o = e.getBoundingClientRect();
                            if ("visible" != n && (s.origin.windowOffset.left < o.left || s.origin.windowOffset.right > o.right))
                                return !(a = !0);
                            if ("visible" != r && (s.origin.windowOffset.top < o.top || s.origin.windowOffset.bottom > o.bottom))
                                return !(a = !0)
                        }
                        return "fixed" != i.css("position") && void 0
                    }),
                    a)
                        e._$tooltip.css("visibility", "hidden");
                    else if (e._$tooltip.css("visibility", "visible"),
                    e.__options.repositionOnScroll)
                        e.reposition(t);
                    else {
                        var i = s.origin.offset.left - e.__Geometry.origin.offset.left
                          , n = s.origin.offset.top - e.__Geometry.origin.offset.top;
                        e._$tooltip.css({
                            left: e.__lastPosition.coord.left + i,
                            top: e.__lastPosition.coord.top + n
                        })
                    }
                }
                e._trigger({
                    type: "scroll",
                    event: t,
                    geo: s
                })
            }
            return e
        },
        __stateSet: function(t) {
            return this.__state = t,
            this._trigger({
                type: "state",
                state: t
            }),
            this
        },
        __timeoutsClear: function() {
            return clearTimeout(this.__timeouts.open),
            this.__timeouts.open = null,
            S.each(this.__timeouts.close, function(t, e) {
                clearTimeout(e)
            }),
            this.__timeouts.close = [],
            this
        },
        __trackerStart: function() {
            var n = this
              , r = n._$tooltip.find(".tooltipster-content");
            return n.__options.trackTooltip && (n.__contentBcr = r[0].getBoundingClientRect()),
            n.__tracker = setInterval(function() {
                if (c(n._$origin) && c(n._$tooltip)) {
                    if (n.__options.trackOrigin) {
                        var t = n.__geometry()
                          , e = !1;
                        o(t.origin.size, n.__Geometry.origin.size) && (n.__Geometry.origin.fixedLineage ? o(t.origin.windowOffset, n.__Geometry.origin.windowOffset) && (e = !0) : o(t.origin.offset, n.__Geometry.origin.offset) && (e = !0)),
                        e || (n.__options.triggerClose.mouseleave ? n._close() : n.reposition())
                    }
                    if (n.__options.trackTooltip) {
                        var i = r[0].getBoundingClientRect();
                        i.height === n.__contentBcr.height && i.width === n.__contentBcr.width || (n.reposition(),
                        n.__contentBcr = i)
                    }
                } else
                    n._close()
            }, n.__options.trackerInterval),
            n
        },
        _close: function(i, t, e) {
            var n = this
              , r = !0;
            if (n._trigger({
                type: "close",
                event: i,
                stop: function() {
                    r = !1
                }
            }),
            r || e) {
                t && n.__callbacks.close.push(t),
                n.__callbacks.open = [],
                n.__timeoutsClear();
                var o = function() {
                    S.each(n.__callbacks.close, function(t, e) {
                        e.call(n, n, {
                            event: i,
                            origin: n._$origin[0]
                        })
                    }),
                    n.__callbacks.close = []
                };
                if ("closed" != n.__state) {
                    var s = !0
                      , a = (new Date).getTime() + n.__options.animationDuration[1];
                    if ("disappearing" == n.__state && a > n.__closingTime && 0 < n.__options.animationDuration[1] && (s = !1),
                    s) {
                        n.__closingTime = a,
                        "disappearing" != n.__state && n.__stateSet("disappearing");
                        var l = function() {
                            clearInterval(n.__tracker),
                            n._trigger({
                                type: "closing",
                                event: i
                            }),
                            n._$tooltip.off("." + n.__namespace + "-triggerClose").removeClass("tooltipster-dying"),
                            S($.window).off("." + n.__namespace + "-triggerClose"),
                            n.__$originParents.each(function(t, e) {
                                S(e).off("scroll." + n.__namespace + "-triggerClose")
                            }),
                            n.__$originParents = null,
                            S($.window.document.body).off("." + n.__namespace + "-triggerClose"),
                            n._$origin.off("." + n.__namespace + "-triggerClose"),
                            n._off("dismissable"),
                            n.__stateSet("closed"),
                            n._trigger({
                                type: "after",
                                event: i
                            }),
                            n.__options.functionAfter && n.__options.functionAfter.call(n, n, {
                                event: i,
                                origin: n._$origin[0]
                            }),
                            o()
                        };
                        $.hasTransitions ? (n._$tooltip.css({
                            "-moz-animation-duration": n.__options.animationDuration[1] + "ms",
                            "-ms-animation-duration": n.__options.animationDuration[1] + "ms",
                            "-o-animation-duration": n.__options.animationDuration[1] + "ms",
                            "-webkit-animation-duration": n.__options.animationDuration[1] + "ms",
                            "animation-duration": n.__options.animationDuration[1] + "ms",
                            "transition-duration": n.__options.animationDuration[1] + "ms"
                        }),
                        n._$tooltip.clearQueue().removeClass("tooltipster-show").addClass("tooltipster-dying"),
                        0 < n.__options.animationDuration[1] && n._$tooltip.delay(n.__options.animationDuration[1]),
                        n._$tooltip.queue(l)) : n._$tooltip.stop().fadeOut(n.__options.animationDuration[1], l)
                    }
                } else
                    o()
            }
            return n
        },
        _off: function() {
            return this.__$emitterPrivate.off.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments)),
            this
        },
        _on: function() {
            return this.__$emitterPrivate.on.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments)),
            this
        },
        _one: function() {
            return this.__$emitterPrivate.one.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments)),
            this
        },
        _open: function(t, e) {
            var i = this;
            if (!i.__destroying && c(i._$origin) && i.__enabled) {
                var n = !0;
                if ("closed" == i.__state && (i._trigger({
                    type: "before",
                    event: t,
                    stop: function() {
                        n = !1
                    }
                }),
                n && i.__options.functionBefore && (n = i.__options.functionBefore.call(i, i, {
                    event: t,
                    origin: i._$origin[0]
                }))),
                !1 !== n && null !== i.__Content) {
                    e && i.__callbacks.open.push(e),
                    i.__callbacks.close = [],
                    i.__timeoutsClear();
                    var r, o = function() {
                        "stable" != i.__state && i.__stateSet("stable"),
                        S.each(i.__callbacks.open, function(t, e) {
                            e.call(i, i, {
                                origin: i._$origin[0],
                                tooltip: i._$tooltip[0]
                            })
                        }),
                        i.__callbacks.open = []
                    };
                    if ("closed" !== i.__state)
                        r = 0,
                        "disappearing" === i.__state ? (i.__stateSet("appearing"),
                        $.hasTransitions ? (i._$tooltip.clearQueue().removeClass("tooltipster-dying").addClass("tooltipster-show"),
                        0 < i.__options.animationDuration[0] && i._$tooltip.delay(i.__options.animationDuration[0]),
                        i._$tooltip.queue(o)) : i._$tooltip.stop().fadeIn(o)) : "stable" == i.__state && o();
                    else {
                        if (i.__stateSet("appearing"),
                        r = i.__options.animationDuration[0],
                        i.__contentInsert(),
                        i.reposition(t, !0),
                        $.hasTransitions ? (i._$tooltip.addClass("tooltipster-" + i.__options.animation).addClass("tooltipster-initial").css({
                            "-moz-animation-duration": i.__options.animationDuration[0] + "ms",
                            "-ms-animation-duration": i.__options.animationDuration[0] + "ms",
                            "-o-animation-duration": i.__options.animationDuration[0] + "ms",
                            "-webkit-animation-duration": i.__options.animationDuration[0] + "ms",
                            "animation-duration": i.__options.animationDuration[0] + "ms",
                            "transition-duration": i.__options.animationDuration[0] + "ms"
                        }),
                        setTimeout(function() {
                            "closed" != i.__state && (i._$tooltip.addClass("tooltipster-show").removeClass("tooltipster-initial"),
                            0 < i.__options.animationDuration[0] && i._$tooltip.delay(i.__options.animationDuration[0]),
                            i._$tooltip.queue(o))
                        }, 0)) : i._$tooltip.css("display", "none").fadeIn(i.__options.animationDuration[0], o),
                        i.__trackerStart(),
                        S($.window).on("resize." + i.__namespace + "-triggerClose", function(t) {
                            var e = S(document.activeElement);
                            (e.is("input") || e.is("textarea")) && S.contains(i._$tooltip[0], e[0]) || i.reposition(t)
                        }).on("scroll." + i.__namespace + "-triggerClose", function(t) {
                            i.__scrollHandler(t)
                        }),
                        i.__$originParents = i._$origin.parents(),
                        i.__$originParents.each(function(t, e) {
                            S(e).on("scroll." + i.__namespace + "-triggerClose", function(t) {
                                i.__scrollHandler(t)
                            })
                        }),
                        i.__options.triggerClose.mouseleave || i.__options.triggerClose.touchleave && $.hasTouchCapability) {
                            i._on("dismissable", function(t) {
                                t.dismissable ? t.delay ? (u = setTimeout(function() {
                                    i._close(t.event)
                                }, t.delay),
                                i.__timeouts.close.push(u)) : i._close(t) : clearTimeout(u)
                            });
                            var s = i._$origin
                              , a = ""
                              , l = ""
                              , u = null;
                            i.__options.interactive && (s = s.add(i._$tooltip)),
                            i.__options.triggerClose.mouseleave && (a += "mouseenter." + i.__namespace + "-triggerClose ",
                            l += "mouseleave." + i.__namespace + "-triggerClose "),
                            i.__options.triggerClose.touchleave && $.hasTouchCapability && (a += "touchstart." + i.__namespace + "-triggerClose",
                            l += "touchend." + i.__namespace + "-triggerClose touchcancel." + i.__namespace + "-triggerClose"),
                            s.on(l, function(t) {
                                if (i._touchIsTouchEvent(t) || !i._touchIsEmulatedEvent(t)) {
                                    var e = "mouseleave" == t.type ? i.__options.delay : i.__options.delayTouch;
                                    i._trigger({
                                        delay: e[1],
                                        dismissable: !0,
                                        event: t,
                                        type: "dismissable"
                                    })
                                }
                            }).on(a, function(t) {
                                !i._touchIsTouchEvent(t) && i._touchIsEmulatedEvent(t) || i._trigger({
                                    dismissable: !1,
                                    event: t,
                                    type: "dismissable"
                                })
                            })
                        }
                        i.__options.triggerClose.originClick && i._$origin.on("click." + i.__namespace + "-triggerClose", function(t) {
                            i._touchIsTouchEvent(t) || i._touchIsEmulatedEvent(t) || i._close(t)
                        }),
                        (i.__options.triggerClose.click || i.__options.triggerClose.tap && $.hasTouchCapability) && setTimeout(function() {
                            if ("closed" != i.__state) {
                                var t = ""
                                  , e = S($.window.document.body);
                                i.__options.triggerClose.click && (t += "click." + i.__namespace + "-triggerClose "),
                                i.__options.triggerClose.tap && $.hasTouchCapability && (t += "touchend." + i.__namespace + "-triggerClose"),
                                e.on(t, function(t) {
                                    i._touchIsMeaningfulEvent(t) && (i._touchRecordEvent(t),
                                    i.__options.interactive && S.contains(i._$tooltip[0], t.target) || i._close(t))
                                }),
                                i.__options.triggerClose.tap && $.hasTouchCapability && e.on("touchstart." + i.__namespace + "-triggerClose", function(t) {
                                    i._touchRecordEvent(t)
                                })
                            }
                        }, 0),
                        i._trigger("ready"),
                        i.__options.functionReady && i.__options.functionReady.call(i, i, {
                            origin: i._$origin[0],
                            tooltip: i._$tooltip[0]
                        })
                    }
                    if (0 < i.__options.timer) {
                        u = setTimeout(function() {
                            i._close()
                        }, i.__options.timer + r);
                        i.__timeouts.close.push(u)
                    }
                }
            }
            return i
        },
        _openShortly: function(t) {
            var e = this
              , i = !0;
            if ("stable" != e.__state && "appearing" != e.__state && !e.__timeouts.open && (e._trigger({
                type: "start",
                event: t,
                stop: function() {
                    i = !1
                }
            }),
            i)) {
                var n = 0 == t.type.indexOf("touch") ? e.__options.delayTouch : e.__options.delay;
                n[0] ? e.__timeouts.open = setTimeout(function() {
                    e.__timeouts.open = null,
                    e.__pointerIsOverOrigin && e._touchIsMeaningfulEvent(t) ? (e._trigger("startend"),
                    e._open(t)) : e._trigger("startcancel")
                }, n[0]) : (e._trigger("startend"),
                e._open(t))
            }
            return e
        },
        _optionsExtract: function(t, e) {
            var n = this
              , i = S.extend(!0, {}, e)
              , r = n.__options[t];
            return r || (r = {},
            S.each(e, function(t, e) {
                var i = n.__options[t];
                void 0 !== i && (r[t] = i)
            })),
            S.each(i, function(t, e) {
                void 0 !== r[t] && ("object" != typeof e || e instanceof Array || null == e || "object" != typeof r[t] || r[t]instanceof Array || null == r[t] ? i[t] = r[t] : S.extend(i[t], r[t]))
            }),
            i
        },
        _plug: function(t) {
            var e = S.tooltipster._plugin(t);
            if (!e)
                throw new Error('The "' + t + '" plugin is not defined');
            return e.instance && S.tooltipster.__bridge(e.instance, this, e.name),
            this
        },
        _touchIsEmulatedEvent: function(t) {
            for (var e = !1, i = (new Date).getTime(), n = this.__touchEvents.length - 1; 0 <= n; n--) {
                var r = this.__touchEvents[n];
                if (!(i - r.time < 500))
                    break;
                r.target === t.target && (e = !0)
            }
            return e
        },
        _touchIsMeaningfulEvent: function(t) {
            return this._touchIsTouchEvent(t) && !this._touchSwiped(t.target) || !this._touchIsTouchEvent(t) && !this._touchIsEmulatedEvent(t)
        },
        _touchIsTouchEvent: function(t) {
            return 0 == t.type.indexOf("touch")
        },
        _touchRecordEvent: function(t) {
            return this._touchIsTouchEvent(t) && (t.time = (new Date).getTime(),
            this.__touchEvents.push(t)),
            this
        },
        _touchSwiped: function(t) {
            for (var e = !1, i = this.__touchEvents.length - 1; 0 <= i; i--) {
                var n = this.__touchEvents[i];
                if ("touchmove" == n.type) {
                    e = !0;
                    break
                }
                if ("touchstart" == n.type && t === n.target)
                    break
            }
            return e
        },
        _trigger: function() {
            var t = Array.prototype.slice.apply(arguments);
            return "string" == typeof t[0] && (t[0] = {
                type: t[0]
            }),
            t[0].instance = this,
            t[0].origin = this._$origin ? this._$origin[0] : null,
            t[0].tooltip = this._$tooltip ? this._$tooltip[0] : null,
            this.__$emitterPrivate.trigger.apply(this.__$emitterPrivate, t),
            S.tooltipster._trigger.apply(S.tooltipster, t),
            this.__$emitterPublic.trigger.apply(this.__$emitterPublic, t),
            this
        },
        _unplug: function(i) {
            var n = this;
            if (n[i]) {
                var t = S.tooltipster._plugin(i);
                t.instance && S.each(t.instance, function(t, e) {
                    n[t] && n[t].bridged === n[i] && delete n[t]
                }),
                n[i].__destroy && n[i].__destroy(),
                delete n[i]
            }
            return n
        },
        close: function(t) {
            return this.__destroyed ? this.__destroyError() : this._close(null, t),
            this
        },
        content: function(t) {
            var e = this;
            if (void 0 === t)
                return e.__Content;
            if (e.__destroyed)
                e.__destroyError();
            else if (e.__contentSet(t),
            null !== e.__Content) {
                if ("closed" !== e.__state && (e.__contentInsert(),
                e.reposition(),
                e.__options.updateAnimation))
                    if ($.hasTransitions) {
                        var i = e.__options.updateAnimation;
                        e._$tooltip.addClass("tooltipster-update-" + i),
                        setTimeout(function() {
                            "closed" != e.__state && e._$tooltip.removeClass("tooltipster-update-" + i)
                        }, 1e3)
                    } else
                        e._$tooltip.fadeTo(200, .5, function() {
                            "closed" != e.__state && e._$tooltip.fadeTo(200, 1)
                        })
            } else
                e._close();
            return e
        },
        destroy: function() {
            var i = this;
            if (i.__destroyed)
                i.__destroyError();
            else {
                "closed" != i.__state ? i.option("animationDuration", 0)._close(null, null, !0) : i.__timeoutsClear(),
                i._trigger("destroy"),
                i.__destroyed = !0,
                i._$origin.removeData(i.__namespace).off("." + i.__namespace + "-triggerOpen"),
                S($.window.document.body).off("." + i.__namespace + "-triggerOpen");
                var t = i._$origin.data("tooltipster-ns");
                if (t)
                    if (1 === t.length) {
                        var e = null;
                        "previous" == i.__options.restoration ? e = i._$origin.data("tooltipster-initialTitle") : "current" == i.__options.restoration && (e = "string" == typeof i.__Content ? i.__Content : S("<div></div>").append(i.__Content).html()),
                        e && i._$origin.attr("title", e),
                        i._$origin.removeClass("tooltipstered"),
                        i._$origin.removeData("tooltipster-ns").removeData("tooltipster-initialTitle")
                    } else
                        t = S.grep(t, function(t, e) {
                            return t !== i.__namespace
                        }),
                        i._$origin.data("tooltipster-ns", t);
                i._trigger("destroyed"),
                i._off(),
                i.off(),
                i.__Content = null,
                i.__$emitterPrivate = null,
                i.__$emitterPublic = null,
                i.__options.parent = null,
                i._$origin = null,
                i._$tooltip = null,
                S.tooltipster.__instancesLatestArr = S.grep(S.tooltipster.__instancesLatestArr, function(t, e) {
                    return i !== t
                }),
                clearInterval(i.__garbageCollector)
            }
            return i
        },
        disable: function() {
            return this.__destroyed ? this.__destroyError() : (this._close(),
            this.__enabled = !1),
            this
        },
        elementOrigin: function() {
            return this.__destroyed ? void this.__destroyError() : this._$origin[0]
        },
        elementTooltip: function() {
            return this._$tooltip ? this._$tooltip[0] : null
        },
        enable: function() {
            return this.__enabled = !0,
            this
        },
        hide: function(t) {
            return this.close(t)
        },
        instance: function() {
            return this
        },
        off: function() {
            return this.__destroyed || this.__$emitterPublic.off.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments)),
            this
        },
        on: function() {
            return this.__destroyed ? this.__destroyError() : this.__$emitterPublic.on.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments)),
            this
        },
        one: function() {
            return this.__destroyed ? this.__destroyError() : this.__$emitterPublic.one.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments)),
            this
        },
        open: function(t) {
            return this.__destroyed ? this.__destroyError() : this._open(null, t),
            this
        },
        option: function(t, e) {
            return void 0 === e ? this.__options[t] : (this.__destroyed ? this.__destroyError() : (this.__options[t] = e,
            this.__optionsFormat(),
            0 <= S.inArray(t, ["trigger", "triggerClose", "triggerOpen"]) && this.__prepareOrigin(),
            "selfDestruction" === t && this.__prepareGC()),
            this)
        },
        reposition: function(t, e) {
            var i = this;
            return i.__destroyed ? i.__destroyError() : "closed" != i.__state && c(i._$origin) && (e || c(i._$tooltip)) && (e || i._$tooltip.detach(),
            i.__Geometry = i.__geometry(),
            i._trigger({
                type: "reposition",
                event: t,
                helper: {
                    geo: i.__Geometry
                }
            })),
            i
        },
        show: function(t) {
            return this.open(t)
        },
        status: function() {
            return {
                destroyed: this.__destroyed,
                enabled: this.__enabled,
                open: "closed" !== this.__state,
                state: this.__state
            }
        },
        triggerHandler: function() {
            return this.__destroyed ? this.__destroyError() : this.__$emitterPublic.triggerHandler.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments)),
            this
        }
    },
    S.fn.tooltipster = function() {
        var r = Array.prototype.slice.apply(arguments)
          , n = "You are using a single HTML element as content for several tooltips. You probably want to set the contentCloning option to TRUE.";
        if (0 === this.length)
            return this;
        if ("string" == typeof r[0]) {
            var o = "#*$~&";
            return this.each(function() {
                var t = S(this).data("tooltipster-ns")
                  , e = t ? S(this).data(t[0]) : null;
                if (!e)
                    throw new Error("You called Tooltipster's \"" + r[0] + '" method on an uninitialized element');
                if ("function" != typeof e[r[0]])
                    throw new Error('Unknown method "' + r[0] + '"');
                1 < this.length && "content" == r[0] && (r[1]instanceof S || "object" == typeof r[1] && null != r[1] && r[1].tagName) && !e.__options.contentCloning && e.__options.debug && console.log(n);
                var i = e[r[0]](r[1], r[2]);
                return i !== e || "instance" === r[0] ? (o = i,
                !1) : void 0
            }),
            "#*$~&" !== o ? o : this
        }
        S.tooltipster.__instancesLatestArr = [];
        var t = r[0] && void 0 !== r[0].multiple
          , s = t && r[0].multiple || !t && h.multiple
          , e = r[0] && void 0 !== r[0].content
          , i = e && r[0].content || !e && h.content
          , a = r[0] && void 0 !== r[0].contentCloning
          , l = a && r[0].contentCloning || !a && h.contentCloning
          , u = r[0] && void 0 !== r[0].debug
          , c = u && r[0].debug || !u && h.debug;
        return 1 < this.length && (i instanceof S || "object" == typeof i && null != i && i.tagName) && !l && c && console.log(n),
        this.each(function() {
            var t = !1
              , e = S(this)
              , i = e.data("tooltipster-ns")
              , n = null;
            i ? s ? t = !0 : c && (console.log("Tooltipster: one or more tooltips are already attached to the element below. Ignoring."),
            console.log(this)) : t = !0,
            t && (n = new S.Tooltipster(this,r[0]),
            i || (i = []),
            i.push(n.__namespace),
            e.data("tooltipster-ns", i),
            e.data(n.__namespace, n),
            n.__options.functionInit && n.__options.functionInit.call(n, n, {
                origin: this
            }),
            n._trigger("init")),
            S.tooltipster.__instancesLatestArr.push(n)
        }),
        this
    }
    ,
    e.prototype = {
        __init: function(t) {
            this.__$tooltip = t,
            this.__$tooltip.css({
                left: 0,
                overflow: "hidden",
                position: "absolute",
                top: 0
            }).find(".tooltipster-content").css("overflow", "auto"),
            this.$container = S('<div class="tooltipster-ruler"></div>').append(this.__$tooltip).appendTo($.window.document.body)
        },
        __forceRedraw: function() {
            var t = this.__$tooltip.parent();
            this.__$tooltip.detach(),
            this.__$tooltip.appendTo(t)
        },
        constrain: function(t, e) {
            return this.constraints = {
                width: t,
                height: e
            },
            this.__$tooltip.css({
                display: "block",
                height: "",
                overflow: "auto",
                width: t
            }),
            this
        },
        destroy: function() {
            this.__$tooltip.detach().find(".tooltipster-content").css({
                display: "",
                overflow: ""
            }),
            this.$container.remove()
        },
        free: function() {
            return this.constraints = null,
            this.__$tooltip.css({
                display: "",
                height: "",
                overflow: "visible",
                width: ""
            }),
            this
        },
        measure: function() {
            this.__forceRedraw();
            var t = this.__$tooltip[0].getBoundingClientRect()
              , e = {
                size: {
                    height: t.height || t.bottom - t.top,
                    width: t.width || t.right - t.left
                }
            };
            if (this.constraints) {
                var i = this.__$tooltip.find(".tooltipster-content")
                  , n = this.__$tooltip.outerHeight()
                  , r = i[0].getBoundingClientRect()
                  , o = {
                    height: n <= this.constraints.height,
                    width: t.width <= this.constraints.width && r.width >= i[0].scrollWidth - 1
                };
                e.fits = o.height && o.width
            }
            return $.IE && $.IE <= 11 && e.size.width !== $.window.document.documentElement.clientWidth && (e.size.width = Math.ceil(e.size.width) + 1),
            e
        }
    };
    var i = navigator.userAgent.toLowerCase();
    -1 != i.indexOf("msie") ? $.IE = parseInt(i.split("msie")[1]) : -1 !== i.toLowerCase().indexOf("trident") && -1 !== i.indexOf(" rv:11") ? $.IE = 11 : -1 != i.toLowerCase().indexOf("edge/") && ($.IE = parseInt(i.toLowerCase().split("edge/")[1]));
    var n = "tooltipster.sideTip";
    return S.tooltipster._plugin({
        name: n,
        instance: {
            __defaults: function() {
                return {
                    arrow: !0,
                    distance: 6,
                    functionPosition: null,
                    maxWidth: null,
                    minIntersection: 16,
                    minWidth: 0,
                    position: null,
                    side: "top",
                    viewportAware: !0
                }
            },
            __init: function(t) {
                var e = this;
                e.__instance = t,
                e.__namespace = "tooltipster-sideTip-" + Math.round(1e6 * Math.random()),
                e.__previousState = "closed",
                e.__options,
                e.__optionsFormat(),
                e.__instance._on("state." + e.__namespace, function(t) {
                    "closed" == t.state ? e.__close() : "appearing" == t.state && "closed" == e.__previousState && e.__create(),
                    e.__previousState = t.state
                }),
                e.__instance._on("options." + e.__namespace, function() {
                    e.__optionsFormat()
                }),
                e.__instance._on("reposition." + e.__namespace, function(t) {
                    e.__reposition(t.event, t.helper)
                })
            },
            __close: function() {
                this.__instance.content()instanceof S && this.__instance.content().detach(),
                this.__instance._$tooltip.remove(),
                this.__instance._$tooltip = null
            },
            __create: function() {
                var t = S('<div class="tooltipster-base tooltipster-sidetip"><div class="tooltipster-box"><div class="tooltipster-content"></div></div><div class="tooltipster-arrow"><div class="tooltipster-arrow-uncropped"><div class="tooltipster-arrow-border"></div><div class="tooltipster-arrow-background"></div></div></div></div>');
                this.__options.arrow || t.find(".tooltipster-box").css("margin", 0).end().find(".tooltipster-arrow").hide(),
                this.__options.minWidth && t.css("min-width", this.__options.minWidth + "px"),
                this.__options.maxWidth && t.css("max-width", this.__options.maxWidth + "px"),
                this.__instance._$tooltip = t,
                this.__instance._trigger("created")
            },
            __destroy: function() {
                this.__instance._off("." + self.__namespace)
            },
            __optionsFormat: function() {
                var t = this;
                if (t.__options = t.__instance._optionsExtract(n, t.__defaults()),
                t.__options.position && (t.__options.side = t.__options.position),
                "object" != typeof t.__options.distance && (t.__options.distance = [t.__options.distance]),
                t.__options.distance.length < 4 && (void 0 === t.__options.distance[1] && (t.__options.distance[1] = t.__options.distance[0]),
                void 0 === t.__options.distance[2] && (t.__options.distance[2] = t.__options.distance[0]),
                void 0 === t.__options.distance[3] && (t.__options.distance[3] = t.__options.distance[1]),
                t.__options.distance = {
                    top: t.__options.distance[0],
                    right: t.__options.distance[1],
                    bottom: t.__options.distance[2],
                    left: t.__options.distance[3]
                }),
                "string" == typeof t.__options.side) {
                    t.__options.side = [t.__options.side, {
                        top: "bottom",
                        right: "left",
                        bottom: "top",
                        left: "right"
                    }[t.__options.side]],
                    "left" == t.__options.side[0] || "right" == t.__options.side[0] ? t.__options.side.push("top", "bottom") : t.__options.side.push("right", "left")
                }
                6 === S.tooltipster._env.IE && !0 !== t.__options.arrow && (t.__options.arrow = !1)
            },
            __reposition: function(l, u) {
                var e, c = this, h = c.__targetFind(u), f = [];
                c.__instance._$tooltip.detach();
                var i = c.__instance._$tooltip.clone()
                  , d = S.tooltipster._getRuler(i)
                  , p = !1
                  , t = c.__instance.option("animation");
                switch (t && i.removeClass("tooltipster-" + t),
                S.each(["window", "document"], function(t, r) {
                    var o = null;
                    if (c.__instance._trigger({
                        container: r,
                        helper: u,
                        satisfied: p,
                        takeTest: function(t) {
                            o = t
                        },
                        results: f,
                        type: "positionTest"
                    }),
                    1 == o || 0 != o && 0 == p && ("window" != r || c.__options.viewportAware))
                        for (t = 0; t < c.__options.side.length; t++) {
                            var s = {
                                horizontal: 0,
                                vertical: 0
                            }
                              , a = c.__options.side[t];
                            "top" == a || "bottom" == a ? s.vertical = c.__options.distance[a] : s.horizontal = c.__options.distance[a],
                            c.__sideChange(i, a),
                            S.each(["natural", "constrained"], function(t, e) {
                                if (o = null,
                                c.__instance._trigger({
                                    container: r,
                                    event: l,
                                    helper: u,
                                    mode: e,
                                    results: f,
                                    satisfied: p,
                                    side: a,
                                    takeTest: function(t) {
                                        o = t
                                    },
                                    type: "positionTest"
                                }),
                                1 == o || 0 != o && 0 == p) {
                                    var i = {
                                        container: r,
                                        distance: s,
                                        fits: null,
                                        mode: e,
                                        outerSize: null,
                                        side: a,
                                        size: null,
                                        target: h[a],
                                        whole: null
                                    }
                                      , n = ("natural" == e ? d.free() : d.constrain(u.geo.available[r][a].width - s.horizontal, u.geo.available[r][a].height - s.vertical)).measure();
                                    if (i.size = n.size,
                                    i.outerSize = {
                                        height: n.size.height + s.vertical,
                                        width: n.size.width + s.horizontal
                                    },
                                    "natural" == e ? u.geo.available[r][a].width >= i.outerSize.width && u.geo.available[r][a].height >= i.outerSize.height ? i.fits = !0 : i.fits = !1 : i.fits = n.fits,
                                    "window" == r && (i.fits ? i.whole = "top" == a || "bottom" == a ? u.geo.origin.windowOffset.right >= c.__options.minIntersection && u.geo.window.size.width - u.geo.origin.windowOffset.left >= c.__options.minIntersection : u.geo.origin.windowOffset.bottom >= c.__options.minIntersection && u.geo.window.size.height - u.geo.origin.windowOffset.top >= c.__options.minIntersection : i.whole = !1),
                                    f.push(i),
                                    i.whole)
                                        p = !0;
                                    else if ("natural" == i.mode && (i.fits || i.size.width <= u.geo.available[r][a].width))
                                        return !1
                                }
                            })
                        }
                }),
                c.__instance._trigger({
                    edit: function(t) {
                        f = t
                    },
                    event: l,
                    helper: u,
                    results: f,
                    type: "positionTested"
                }),
                f.sort(function(t, e) {
                    return t.whole && !e.whole ? -1 : !t.whole && e.whole ? 1 : t.whole && e.whole ? (i = c.__options.side.indexOf(t.side)) < (n = c.__options.side.indexOf(e.side)) ? -1 : n < i ? 1 : "natural" == t.mode ? -1 : 1 : t.fits && !e.fits ? -1 : !t.fits && e.fits ? 1 : t.fits && e.fits ? (i = c.__options.side.indexOf(t.side)) < (n = c.__options.side.indexOf(e.side)) ? -1 : n < i ? 1 : "natural" == t.mode ? -1 : 1 : "document" == t.container && "bottom" == t.side && "natural" == t.mode ? -1 : 1;
                    var i, n
                }),
                (e = f[0]).coord = {},
                e.side) {
                case "left":
                case "right":
                    e.coord.top = Math.floor(e.target - e.size.height / 2);
                    break;
                case "bottom":
                case "top":
                    e.coord.left = Math.floor(e.target - e.size.width / 2)
                }
                switch (e.side) {
                case "left":
                    e.coord.left = u.geo.origin.windowOffset.left - e.outerSize.width;
                    break;
                case "right":
                    e.coord.left = u.geo.origin.windowOffset.right + e.distance.horizontal;
                    break;
                case "top":
                    e.coord.top = u.geo.origin.windowOffset.top - e.outerSize.height;
                    break;
                case "bottom":
                    e.coord.top = u.geo.origin.windowOffset.bottom + e.distance.vertical
                }
                "window" == e.container ? "top" == e.side || "bottom" == e.side ? e.coord.left < 0 ? 0 <= u.geo.origin.windowOffset.right - this.__options.minIntersection ? e.coord.left = 0 : e.coord.left = u.geo.origin.windowOffset.right - this.__options.minIntersection - 1 : e.coord.left > u.geo.window.size.width - e.size.width && (u.geo.origin.windowOffset.left + this.__options.minIntersection <= u.geo.window.size.width ? e.coord.left = u.geo.window.size.width - e.size.width : e.coord.left = u.geo.origin.windowOffset.left + this.__options.minIntersection + 1 - e.size.width) : e.coord.top < 0 ? 0 <= u.geo.origin.windowOffset.bottom - this.__options.minIntersection ? e.coord.top = 0 : e.coord.top = u.geo.origin.windowOffset.bottom - this.__options.minIntersection - 1 : e.coord.top > u.geo.window.size.height - e.size.height && (u.geo.origin.windowOffset.top + this.__options.minIntersection <= u.geo.window.size.height ? e.coord.top = u.geo.window.size.height - e.size.height : e.coord.top = u.geo.origin.windowOffset.top + this.__options.minIntersection + 1 - e.size.height) : (e.coord.left > u.geo.window.size.width - e.size.width && (e.coord.left = u.geo.window.size.width - e.size.width),
                e.coord.left < 0 && (e.coord.left = 0)),
                c.__sideChange(i, e.side),
                u.tooltipClone = i[0],
                u.tooltipParent = c.__instance.option("parent").parent[0],
                u.mode = e.mode,
                u.whole = e.whole,
                u.origin = c.__instance._$origin[0],
                u.tooltip = c.__instance._$tooltip[0],
                delete e.container,
                delete e.fits,
                delete e.mode,
                delete e.outerSize,
                delete e.whole,
                e.distance = e.distance.horizontal || e.distance.vertical;
                var n, r, o, s = S.extend(!0, {}, e);
                if (c.__instance._trigger({
                    edit: function(t) {
                        e = t
                    },
                    event: l,
                    helper: u,
                    position: s,
                    type: "position"
                }),
                c.__options.functionPosition) {
                    var a = c.__options.functionPosition.call(c, c.__instance, u, s);
                    a && (e = a)
                }
                d.destroy(),
                r = "top" == e.side || "bottom" == e.side ? (n = {
                    prop: "left",
                    val: e.target - e.coord.left
                },
                e.size.width - this.__options.minIntersection) : (n = {
                    prop: "top",
                    val: e.target - e.coord.top
                },
                e.size.height - this.__options.minIntersection),
                n.val < this.__options.minIntersection ? n.val = this.__options.minIntersection : n.val > r && (n.val = r),
                o = u.geo.origin.fixedLineage ? u.geo.origin.windowOffset : {
                    left: u.geo.origin.windowOffset.left + u.geo.window.scroll.left,
                    top: u.geo.origin.windowOffset.top + u.geo.window.scroll.top
                },
                e.coord = {
                    left: o.left + (e.coord.left - u.geo.origin.windowOffset.left),
                    top: o.top + (e.coord.top - u.geo.origin.windowOffset.top)
                },
                c.__sideChange(c.__instance._$tooltip, e.side),
                u.geo.origin.fixedLineage ? c.__instance._$tooltip.css("position", "fixed") : c.__instance._$tooltip.css("position", ""),
                c.__instance._$tooltip.css({
                    left: e.coord.left,
                    top: e.coord.top,
                    height: e.size.height,
                    width: e.size.width
                }).find(".tooltipster-arrow").css({
                    left: "",
                    top: ""
                }).css(n.prop, n.val),
                c.__instance._$tooltip.appendTo(c.__instance.option("parent")),
                c.__instance._trigger({
                    type: "repositioned",
                    event: l,
                    position: e
                })
            },
            __sideChange: function(t, e) {
                t.removeClass("tooltipster-bottom").removeClass("tooltipster-left").removeClass("tooltipster-right").removeClass("tooltipster-top").addClass("tooltipster-" + e)
            },
            __targetFind: function(t) {
                var e = {}
                  , i = this.__instance._$origin[0].getClientRects();
                1 < i.length && (1 == this.__instance._$origin.css("opacity") && (this.__instance._$origin.css("opacity", .99),
                i = this.__instance._$origin[0].getClientRects(),
                this.__instance._$origin.css("opacity", 1)));
                if (i.length < 2)
                    e.top = Math.floor(t.geo.origin.windowOffset.left + t.geo.origin.size.width / 2),
                    e.bottom = e.top,
                    e.left = Math.floor(t.geo.origin.windowOffset.top + t.geo.origin.size.height / 2),
                    e.right = e.left;
                else {
                    var n = i[0];
                    e.top = Math.floor(n.left + (n.right - n.left) / 2),
                    n = 2 < i.length ? i[Math.ceil(i.length / 2) - 1] : i[0],
                    e.right = Math.floor(n.top + (n.bottom - n.top) / 2),
                    n = i[i.length - 1],
                    e.bottom = Math.floor(n.left + (n.right - n.left) / 2),
                    n = 2 < i.length ? i[Math.ceil((i.length + 1) / 2) - 1] : i[i.length - 1],
                    e.left = Math.floor(n.top + (n.bottom - n.top) / 2)
                }
                return e
            }
        }
    }),
    S
}),
((_gsScope = "undefined" != typeof module && module.exports && "undefined" != typeof global ? global : this || window)._gsQueue || (_gsScope._gsQueue = [])).push(function() {
    "use strict";
    _gsScope._gsDefine("easing.CustomEase", ["easing.Ease"], function(m) {
        var g = /(?:(-|-=|\+=)?\d*\.?\d*(?:e[\-+]?\d+)?)[0-9]/gi
          , v = /[achlmqstvz]|(-?\d*\.?\d*(?:e[\-+]?\d+)?)[0-9]/gi
          , y = /[\+\-]?\d*\.?\d+e[\+\-]?\d+/gi
          , _ = /[cLlsS]/g
          , w = "CustomEase only accepts Cubic Bezier data."
          , D = function(t, e, i, n, r, o, s, a, l, u, c) {
            var h, f = (t + i) / 2, d = (e + n) / 2, p = (i + r) / 2, g = (n + o) / 2, _ = (r + s) / 2, m = (o + a) / 2, v = (f + p) / 2, y = (d + g) / 2, w = (p + _) / 2, b = (g + m) / 2, x = (v + w) / 2, T = (y + b) / 2, C = s - t, k = a - e, S = Math.abs((i - s) * k - (n - a) * C), $ = Math.abs((r - s) * k - (o - a) * C);
            return u || (u = [{
                x: t,
                y: e
            }, {
                x: s,
                y: a
            }],
            c = 1),
            u.splice(c || u.length - 1, 0, {
                x: x,
                y: T
            }),
            l * (C * C + k * k) < (S + $) * (S + $) && (h = u.length,
            D(t, e, f, d, v, y, x, T, l, u, c),
            D(x, T, w, b, _, m, s, a, l, u, c + 1 + (u.length - h))),
            u
        }
          , n = function(t) {
            var e = this.lookup[t * this.l | 0] || this.lookup[this.l - 1];
            return e.nx < t && (e = e.n),
            e.y + (t - e.x) / e.cx * e.cy
        }
          , r = function(t, e, i) {
            this._calcEnd = !0,
            (this.id = t) && (m.map[t] = this),
            this.getRatio = n,
            this.setData(e, i)
        }
          , t = r.prototype = new m;
        return t.constructor = r,
        t.setData = function(t, e) {
            var i, n, r, o, s, a, l, u, c, h, f = (t = t || "0,0,1,1").match(g), d = 1, p = [];
            if (h = (e = e || {}).precision || 1,
            this.data = t,
            this.lookup = [],
            this.points = p,
            this.fast = h <= 1,
            (_.test(t) || -1 !== t.indexOf("M") && -1 === t.indexOf("C")) && (f = function(t) {
                var e, i, n, r, o, s, a, l, u, c, h, f = (t + "").replace(y, function(t) {
                    var e = +t;
                    return e < 1e-4 && -1e-4 < e ? 0 : e
                }).match(v) || [], d = [], p = 0, g = 0, _ = f.length, m = 2;
                for (e = 0; e < _; e++)
                    if (u = r,
                    isNaN(f[e]) ? o = (r = f[e].toUpperCase()) !== f[e] : e--,
                    i = +f[e + 1],
                    n = +f[e + 2],
                    o && (i += p,
                    n += g),
                    e || (a = i,
                    l = n),
                    "M" === r)
                        s && s.length < 8 && (d.length -= 1,
                        m = 0),
                        p = a = i,
                        g = l = n,
                        s = [i, n],
                        m = 2,
                        d.push(s),
                        e += 2,
                        r = "L";
                    else if ("C" === r)
                        s || (s = [0, 0]),
                        s[m++] = i,
                        s[m++] = n,
                        o || (p = g = 0),
                        s[m++] = p + 1 * f[e + 3],
                        s[m++] = g + 1 * f[e + 4],
                        s[m++] = p += 1 * f[e + 5],
                        s[m++] = g += 1 * f[e + 6],
                        e += 6;
                    else if ("S" === r)
                        s[m++] = "C" === u || "S" === u ? (c = p - s[m - 4],
                        h = g - s[m - 3],
                        s[m++] = p + c,
                        g + h) : (s[m++] = p,
                        g),
                        s[m++] = i,
                        s[m++] = n,
                        o || (p = g = 0),
                        s[m++] = p += 1 * f[e + 3],
                        s[m++] = g += 1 * f[e + 4],
                        e += 4;
                    else {
                        if ("L" !== r && "Z" !== r)
                            throw w;
                        "Z" === r && (i = a,
                        n = l,
                        s.closed = !0),
                        ("L" === r || .5 < Math.abs(p - i) || .5 < Math.abs(g - n)) && (s[m++] = p + (i - p) / 3,
                        s[m++] = g + (n - g) / 3,
                        s[m++] = p + 2 * (i - p) / 3,
                        s[m++] = g + 2 * (n - g) / 3,
                        s[m++] = i,
                        s[m++] = n,
                        "L" === r && (e += 2)),
                        p = i,
                        g = n
                    }
                return d[0]
            }(t)),
            4 === (i = f.length))
                f.unshift(0, 0),
                f.push(1, 1),
                i = 8;
            else if ((i - 2) % 6)
                throw w;
            for ((0 != +f[0] || 1 != +f[i - 2]) && function(t, e, i) {
                i || 0 === i || (i = Math.max(+t[t.length - 1], +t[1]));
                var n, r = -1 * +t[0], o = -i, s = t.length, a = 1 / (+t[s - 2] + r), l = -e || (Math.abs(+t[s - 1] - +t[1]) < .01 * (+t[s - 2] - +t[0]) ? function(t) {
                    var e, i = t.length, n = 999999999999;
                    for (e = 1; e < i; e += 6)
                        +t[e] < n && (n = +t[e]);
                    return n
                }(t) + o : +t[s - 1] + o);
                for (l = l ? 1 / l : -a,
                n = 0; n < s; n += 2)
                    t[n] = (+t[n] + r) * a,
                    t[n + 1] = (+t[n + 1] + o) * l
            }(f, e.height, e.originY),
            this.rawBezier = f,
            o = 2; o < i; o += 6)
                n = {
                    x: +f[o - 2],
                    y: +f[o - 1]
                },
                r = {
                    x: +f[o + 4],
                    y: +f[o + 5]
                },
                p.push(n, r),
                D(n.x, n.y, +f[o], +f[o + 1], +f[o + 2], +f[o + 3], r.x, r.y, 1 / (2e5 * h), p, p.length - 1);
            for (i = p.length,
            o = 0; o < i; o++)
                l = p[o],
                u = p[o - 1] || l,
                l.x > u.x || u.y !== l.y && u.x === l.x || l === u ? (u.cx = l.x - u.x,
                u.cy = l.y - u.y,
                u.n = l,
                u.nx = l.x,
                this.fast && 1 < o && 2 < Math.abs(u.cy / u.cx - p[o - 2].cy / p[o - 2].cx) && (this.fast = !1),
                u.cx < d && (u.cx ? d = u.cx : (u.cx = .001,
                o === i - 1 && (u.x -= .001,
                d = Math.min(d, .001),
                this.fast = !1)))) : (p.splice(o--, 1),
                i--);
            if (i = 1 / d + 1 | 0,
            s = 1 / (this.l = i),
            l = p[a = 0],
            this.fast) {
                for (o = 0; o < i; o++)
                    c = o * s,
                    l.nx < c && (l = p[++a]),
                    n = l.y + (c - l.x) / l.cx * l.cy,
                    this.lookup[o] = {
                        x: c,
                        cx: s,
                        y: n,
                        cy: 0,
                        nx: 9
                    },
                    o && (this.lookup[o - 1].cy = n - this.lookup[o - 1].y);
                this.lookup[i - 1].cy = p[p.length - 1].y - n
            } else {
                for (o = 0; o < i; o++)
                    l.nx < o * s && (l = p[++a]),
                    this.lookup[o] = l;
                a < p.length - 1 && (this.lookup[o - 1] = p[p.length - 2])
            }
            return this._calcEnd = 1 !== p[p.length - 1].y || 0 !== p[0].y,
            this
        }
        ,
        t.getRatio = n,
        t.getSVGData = function(t) {
            return r.getSVGData(this, t)
        }
        ,
        r.create = function(t, e, i) {
            return new r(t,e,i)
        }
        ,
        r.version = "0.2.2",
        r.bezierToPoints = D,
        r.get = function(t) {
            return m.map[t]
        }
        ,
        r.getSVGData = function(t, e) {
            var i, n, r, o, s, a, l, u, c, h, f = (e = e || {}).width || 100, d = e.height || 100, p = e.x || 0, g = (e.y || 0) + d, _ = e.path;
            if (e.invert && (d = -d,
            g = 0),
            (t = t.getRatio ? t : m.map[t] || console.log("No ease found: ", t)).rawBezier) {
                for (i = [],
                l = t.rawBezier.length,
                r = 0; r < l; r += 2)
                    i.push((1e3 * (p + t.rawBezier[r] * f) | 0) / 1e3 + "," + (1e3 * (g + t.rawBezier[r + 1] * -d) | 0) / 1e3);
                i[0] = "M" + i[0],
                i[1] = "C" + i[1]
            } else
                for (i = ["M" + p + "," + g],
                o = 1 / (l = Math.max(5, 200 * (e.precision || 1))),
                u = 5 / (l += 2),
                c = (1e3 * (p + o * f) | 0) / 1e3,
                n = ((h = (1e3 * (g + t.getRatio(o) * -d) | 0) / 1e3) - g) / (c - p),
                r = 2; r < l; r++)
                    s = (1e3 * (p + r * o * f) | 0) / 1e3,
                    a = (1e3 * (g + t.getRatio(r * o) * -d) | 0) / 1e3,
                    (Math.abs((a - h) / (s - c) - n) > u || r === l - 1) && (i.push(c + "," + h),
                    n = (a - h) / (s - c)),
                    c = s,
                    h = a;
            return _ && ("string" == typeof _ ? document.querySelector(_) : _).setAttribute("d", i.join(" ")),
            i.join(" ")
        }
        ,
        r
    }, !0)
}),
_gsScope._gsDefine && _gsScope._gsQueue.pop()(),
function(t) {
    "use strict";
    var e = function() {
        return (_gsScope.GreenSockGlobals || _gsScope).CustomEase
    };
    "undefined" != typeof module && module.exports ? (require("../TweenLite.min.js"),
    module.exports = e()) : "function" == typeof define && define.amd && define(["TweenLite"], e)
}(),
((_gsScope = "undefined" != typeof module && module.exports && "undefined" != typeof global ? global : this || window)._gsQueue || (_gsScope._gsQueue = [])).push(function() {
    "use strict";
    var t, l, u, e, T, b, x, C, m, i, v, k, y, w, d, p, _, n;
    _gsScope._gsDefine("TweenMax", ["core.Animation", "core.SimpleTimeline", "TweenLite"], function(n, c, v) {
        var _ = function(t) {
            var e, i = [], n = t.length;
            for (e = 0; e !== n; i.push(t[e++]))
                ;
            return i
        }
          , m = function(t, e, i) {
            var n, r, o = t.cycle;
            for (n in o)
                r = o[n],
                t[n] = "function" == typeof r ? r(i, e[i]) : r[i % r.length];
            delete t.cycle
        }
          , y = function(t, e, i) {
            v.call(this, t, e, i),
            this._cycle = 0,
            this._yoyo = !0 === this.vars.yoyo || !!this.vars.yoyoEase,
            this._repeat = this.vars.repeat || 0,
            this._repeatDelay = this.vars.repeatDelay || 0,
            this._repeat && this._uncache(!0),
            this.render = y.prototype.render
        }
          , w = 1e-10
          , b = v._internals
          , x = b.isSelector
          , T = b.isArray
          , t = y.prototype = v.to({}, .1, {})
          , C = [];
        y.version = "2.0.2",
        t.constructor = y,
        t.kill()._gc = !1,
        y.killTweensOf = y.killDelayedCallsTo = v.killTweensOf,
        y.getTweensOf = v.getTweensOf,
        y.lagSmoothing = v.lagSmoothing,
        y.ticker = v.ticker,
        y.render = v.render,
        t.invalidate = function() {
            return this._yoyo = !0 === this.vars.yoyo || !!this.vars.yoyoEase,
            this._repeat = this.vars.repeat || 0,
            this._repeatDelay = this.vars.repeatDelay || 0,
            this._yoyoEase = null,
            this._uncache(!0),
            v.prototype.invalidate.call(this)
        }
        ,
        t.updateTo = function(t, e) {
            var i, n = this.ratio, r = this.vars.immediateRender || t.immediateRender;
            for (i in e && this._startTime < this._timeline._time && (this._startTime = this._timeline._time,
            this._uncache(!1),
            this._gc ? this._enabled(!0, !1) : this._timeline.insert(this, this._startTime - this._delay)),
            t)
                this.vars[i] = t[i];
            if (this._initted || r)
                if (e)
                    this._initted = !1,
                    r && this.render(0, !0, !0);
                else if (this._gc && this._enabled(!0, !1),
                this._notifyPluginsOfEnabled && this._firstPT && v._onPluginEvent("_onDisable", this),
                .998 < this._time / this._duration) {
                    var o = this._totalTime;
                    this.render(0, !0, !1),
                    this._initted = !1,
                    this.render(o, !0, !1)
                } else if (this._initted = !1,
                this._init(),
                0 < this._time || r)
                    for (var s, a = 1 / (1 - n), l = this._firstPT; l; )
                        s = l.s + l.c,
                        l.c *= a,
                        l.s = s - l.c,
                        l = l._next;
            return this
        }
        ,
        t.render = function(t, e, i) {
            this._initted || 0 === this._duration && this.vars.repeat && this.invalidate();
            var n, r, o, s, a, l, u, c, h, f = this._dirty ? this.totalDuration() : this._totalDuration, d = this._time, p = this._totalTime, g = this._cycle, _ = this._duration, m = this._rawPrevTime;
            if (f - 1e-7 <= t && 0 <= t ? (this._totalTime = f,
            this._cycle = this._repeat,
            this._yoyo && 0 != (1 & this._cycle) ? (this._time = 0,
            this.ratio = this._ease._calcEnd ? this._ease.getRatio(0) : 0) : (this._time = _,
            this.ratio = this._ease._calcEnd ? this._ease.getRatio(1) : 1),
            this._reversed || (n = !0,
            r = "onComplete",
            i = i || this._timeline.autoRemoveChildren),
            0 === _ && (this._initted || !this.vars.lazy || i) && (this._startTime === this._timeline._duration && (t = 0),
            (m < 0 || t <= 0 && -1e-7 <= t || m === w && "isPause" !== this.data) && m !== t && (i = !0,
            w < m && (r = "onReverseComplete")),
            this._rawPrevTime = c = !e || t || m === t ? t : w)) : t < 1e-7 ? (this._totalTime = this._time = this._cycle = 0,
            this.ratio = this._ease._calcEnd ? this._ease.getRatio(0) : 0,
            (0 !== p || 0 === _ && 0 < m) && (r = "onReverseComplete",
            n = this._reversed),
            t < 0 && (this._active = !1,
            0 === _ && (this._initted || !this.vars.lazy || i) && (0 <= m && (i = !0),
            this._rawPrevTime = c = !e || t || m === t ? t : w)),
            this._initted || (i = !0)) : (this._totalTime = this._time = t,
            0 !== this._repeat && (s = _ + this._repeatDelay,
            this._cycle = this._totalTime / s >> 0,
            0 !== this._cycle && this._cycle === this._totalTime / s && p <= t && this._cycle--,
            this._time = this._totalTime - this._cycle * s,
            this._yoyo && 0 != (1 & this._cycle) && (this._time = _ - this._time,
            (h = this._yoyoEase || this.vars.yoyoEase) && (this._yoyoEase || (!0 !== h || this._initted ? this._yoyoEase = h = !0 === h ? this._ease : h instanceof Ease ? h : Ease.map[h] : (h = this.vars.ease,
            this._yoyoEase = h = h ? h instanceof Ease ? h : "function" == typeof h ? new Ease(h,this.vars.easeParams) : Ease.map[h] || v.defaultEase : v.defaultEase)),
            this.ratio = h ? 1 - h.getRatio((_ - this._time) / _) : 0)),
            this._time > _ ? this._time = _ : this._time < 0 && (this._time = 0)),
            this._easeType && !h ? (a = this._time / _,
            (1 === (l = this._easeType) || 3 === l && .5 <= a) && (a = 1 - a),
            3 === l && (a *= 2),
            1 === (u = this._easePower) ? a *= a : 2 === u ? a *= a * a : 3 === u ? a *= a * a * a : 4 === u && (a *= a * a * a * a),
            1 === l ? this.ratio = 1 - a : 2 === l ? this.ratio = a : this._time / _ < .5 ? this.ratio = a / 2 : this.ratio = 1 - a / 2) : h || (this.ratio = this._ease.getRatio(this._time / _))),
            d !== this._time || i || g !== this._cycle) {
                if (!this._initted) {
                    if (this._init(),
                    !this._initted || this._gc)
                        return;
                    if (!i && this._firstPT && (!1 !== this.vars.lazy && this._duration || this.vars.lazy && !this._duration))
                        return this._time = d,
                        this._totalTime = p,
                        this._rawPrevTime = m,
                        this._cycle = g,
                        b.lazyTweens.push(this),
                        void (this._lazy = [t, e]);
                    !this._time || n || h ? n && this._ease._calcEnd && !h && (this.ratio = this._ease.getRatio(0 === this._time ? 0 : 1)) : this.ratio = this._ease.getRatio(this._time / _)
                }
                for (!1 !== this._lazy && (this._lazy = !1),
                this._active || !this._paused && this._time !== d && 0 <= t && (this._active = !0),
                0 === p && (2 === this._initted && 0 < t && this._init(),
                this._startAt && (0 <= t ? this._startAt.render(t, !0, i) : r || (r = "_dummyGS")),
                this.vars.onStart && (0 !== this._totalTime || 0 === _) && (e || this._callback("onStart"))),
                o = this._firstPT; o; )
                    o.f ? o.t[o.p](o.c * this.ratio + o.s) : o.t[o.p] = o.c * this.ratio + o.s,
                    o = o._next;
                this._onUpdate && (t < 0 && this._startAt && this._startTime && this._startAt.render(t, !0, i),
                e || (this._totalTime !== p || r) && this._callback("onUpdate")),
                this._cycle !== g && (e || this._gc || this.vars.onRepeat && this._callback("onRepeat")),
                r && (!this._gc || i) && (t < 0 && this._startAt && !this._onUpdate && this._startTime && this._startAt.render(t, !0, i),
                n && (this._timeline.autoRemoveChildren && this._enabled(!1, !1),
                this._active = !1),
                !e && this.vars[r] && this._callback(r),
                0 === _ && this._rawPrevTime === w && c !== w && (this._rawPrevTime = 0))
            } else
                p !== this._totalTime && this._onUpdate && (e || this._callback("onUpdate"))
        }
        ,
        y.to = function(t, e, i) {
            return new y(t,e,i)
        }
        ,
        y.from = function(t, e, i) {
            return i.runBackwards = !0,
            i.immediateRender = 0 != i.immediateRender,
            new y(t,e,i)
        }
        ,
        y.fromTo = function(t, e, i, n) {
            return n.startAt = i,
            n.immediateRender = 0 != n.immediateRender && 0 != i.immediateRender,
            new y(t,e,n)
        }
        ,
        y.staggerTo = y.allTo = function(t, e, i, n, r, o, s) {
            n = n || 0;
            var a, l, u, c, h = 0, f = [], d = function() {
                i.onComplete && i.onComplete.apply(i.onCompleteScope || this, arguments),
                r.apply(s || i.callbackScope || this, o || C)
            }, p = i.cycle, g = i.startAt && i.startAt.cycle;
            for (T(t) || ("string" == typeof t && (t = v.selector(t) || t),
            x(t) && (t = _(t))),
            t = t || [],
            n < 0 && ((t = _(t)).reverse(),
            n *= -1),
            a = t.length - 1,
            u = 0; u <= a; u++) {
                for (c in l = {},
                i)
                    l[c] = i[c];
                if (p && (m(l, t, u),
                null != l.duration && (e = l.duration,
                delete l.duration)),
                g) {
                    for (c in g = l.startAt = {},
                    i.startAt)
                        g[c] = i.startAt[c];
                    m(l.startAt, t, u)
                }
                l.delay = h + (l.delay || 0),
                u === a && r && (l.onComplete = d),
                f[u] = new y(t[u],e,l),
                h += n
            }
            return f
        }
        ,
        y.staggerFrom = y.allFrom = function(t, e, i, n, r, o, s) {
            return i.runBackwards = !0,
            i.immediateRender = 0 != i.immediateRender,
            y.staggerTo(t, e, i, n, r, o, s)
        }
        ,
        y.staggerFromTo = y.allFromTo = function(t, e, i, n, r, o, s, a) {
            return n.startAt = i,
            n.immediateRender = 0 != n.immediateRender && 0 != i.immediateRender,
            y.staggerTo(t, e, n, r, o, s, a)
        }
        ,
        y.delayedCall = function(t, e, i, n, r) {
            return new y(e,0,{
                delay: t,
                onComplete: e,
                onCompleteParams: i,
                callbackScope: n,
                onReverseComplete: e,
                onReverseCompleteParams: i,
                immediateRender: !1,
                useFrames: r,
                overwrite: 0
            })
        }
        ,
        y.set = function(t, e) {
            return new y(t,0,e)
        }
        ,
        y.isTweening = function(t) {
            return 0 < v.getTweensOf(t, !0).length
        }
        ;
        var o = function(t, e) {
            for (var i = [], n = 0, r = t._first; r; )
                r instanceof v ? i[n++] = r : (e && (i[n++] = r),
                n = (i = i.concat(o(r, e))).length),
                r = r._next;
            return i
        }
          , h = y.getAllTweens = function(t) {
            return o(n._rootTimeline, t).concat(o(n._rootFramesTimeline, t))
        }
        ;
        y.killAll = function(t, e, i, n) {
            null == e && (e = !0),
            null == i && (i = !0);
            var r, o, s, a = h(0 != n), l = a.length, u = e && i && n;
            for (s = 0; s < l; s++)
                o = a[s],
                (u || o instanceof c || (r = o.target === o.vars.onComplete) && i || e && !r) && (t ? o.totalTime(o._reversed ? 0 : o.totalDuration()) : o._enabled(!1, !1))
        }
        ,
        y.killChildTweensOf = function(t, e) {
            if (null != t) {
                var i, n, r, o, s, a = b.tweenLookup;
                if ("string" == typeof t && (t = v.selector(t) || t),
                x(t) && (t = _(t)),
                T(t))
                    for (o = t.length; -1 < --o; )
                        y.killChildTweensOf(t[o], e);
                else {
                    for (r in i = [],
                    a)
                        for (n = a[r].target.parentNode; n; )
                            n === t && (i = i.concat(a[r].tweens)),
                            n = n.parentNode;
                    for (s = i.length,
                    o = 0; o < s; o++)
                        e && i[o].totalTime(i[o].totalDuration()),
                        i[o]._enabled(!1, !1)
                }
            }
        }
        ;
        var r = function(t, e, i, n) {
            e = !1 !== e,
            i = !1 !== i;
            for (var r, o, s = h(n = !1 !== n), a = e && i && n, l = s.length; -1 < --l; )
                o = s[l],
                (a || o instanceof c || (r = o.target === o.vars.onComplete) && i || e && !r) && o.paused(t)
        };
        return y.pauseAll = function(t, e, i) {
            r(!0, t, e, i)
        }
        ,
        y.resumeAll = function(t, e, i) {
            r(!1, t, e, i)
        }
        ,
        y.globalTimeScale = function(t) {
            var e = n._rootTimeline
              , i = v.ticker.time;
            return arguments.length ? (t = t || w,
            e._startTime = i - (i - e._startTime) * e._timeScale / t,
            e = n._rootFramesTimeline,
            i = v.ticker.frame,
            e._startTime = i - (i - e._startTime) * e._timeScale / t,
            e._timeScale = n._rootTimeline._timeScale = t,
            t) : e._timeScale
        }
        ,
        t.progress = function(t, e) {
            return arguments.length ? this.totalTime(this.duration() * (this._yoyo && 0 != (1 & this._cycle) ? 1 - t : t) + this._cycle * (this._duration + this._repeatDelay), e) : this._time / this.duration()
        }
        ,
        t.totalProgress = function(t, e) {
            return arguments.length ? this.totalTime(this.totalDuration() * t, e) : this._totalTime / this.totalDuration()
        }
        ,
        t.time = function(t, e) {
            return arguments.length ? (this._dirty && this.totalDuration(),
            t > this._duration && (t = this._duration),
            this._yoyo && 0 != (1 & this._cycle) ? t = this._duration - t + this._cycle * (this._duration + this._repeatDelay) : 0 !== this._repeat && (t += this._cycle * (this._duration + this._repeatDelay)),
            this.totalTime(t, e)) : this._time
        }
        ,
        t.duration = function(t) {
            return arguments.length ? n.prototype.duration.call(this, t) : this._duration
        }
        ,
        t.totalDuration = function(t) {
            return arguments.length ? -1 === this._repeat ? this : this.duration((t - this._repeat * this._repeatDelay) / (this._repeat + 1)) : (this._dirty && (this._totalDuration = -1 === this._repeat ? 999999999999 : this._duration * (this._repeat + 1) + this._repeatDelay * this._repeat,
            this._dirty = !1),
            this._totalDuration)
        }
        ,
        t.repeat = function(t) {
            return arguments.length ? (this._repeat = t,
            this._uncache(!0)) : this._repeat
        }
        ,
        t.repeatDelay = function(t) {
            return arguments.length ? (this._repeatDelay = t,
            this._uncache(!0)) : this._repeatDelay
        }
        ,
        t.yoyo = function(t) {
            return arguments.length ? (this._yoyo = t,
            this) : this._yoyo
        }
        ,
        y
    }, !0),
    _gsScope._gsDefine("TimelineLite", ["core.Animation", "core.SimpleTimeline", "TweenLite"], function(c, h, f) {
        var d = function(t) {
            h.call(this, t),
            this._labels = {},
            this.autoRemoveChildren = !0 === this.vars.autoRemoveChildren,
            this.smoothChildTiming = !0 === this.vars.smoothChildTiming,
            this._sortChildren = !0,
            this._onUpdate = this.vars.onUpdate;
            var e, i, n = this.vars;
            for (i in n)
                e = n[i],
                g(e) && -1 !== e.join("").indexOf("{self}") && (n[i] = this._swapSelfInParams(e));
            g(n.tweens) && this.add(n.tweens, 0, n.align, n.stagger)
        }
          , t = f._internals
          , e = d._internals = {}
          , p = t.isSelector
          , g = t.isArray
          , _ = t.lazyTweens
          , m = t.lazyRender
          , s = _gsScope._gsDefine.globals
          , v = function(t) {
            var e, i = {};
            for (e in t)
                i[e] = t[e];
            return i
        }
          , y = function(t, e, i) {
            var n, r, o = t.cycle;
            for (n in o)
                r = o[n],
                t[n] = "function" == typeof r ? r(i, e[i]) : r[i % r.length];
            delete t.cycle
        }
          , o = e.pauseCallback = function() {}
          , w = function(t) {
            var e, i = [], n = t.length;
            for (e = 0; e !== n; i.push(t[e++]))
                ;
            return i
        }
          , i = d.prototype = new h;
        return d.version = "2.0.2",
        i.constructor = d,
        i.kill()._gc = i._forcingPlayhead = i._hasPause = !1,
        i.to = function(t, e, i, n) {
            var r = i.repeat && s.TweenMax || f;
            return e ? this.add(new r(t,e,i), n) : this.set(t, i, n)
        }
        ,
        i.from = function(t, e, i, n) {
            return this.add((i.repeat && s.TweenMax || f).from(t, e, i), n)
        }
        ,
        i.fromTo = function(t, e, i, n, r) {
            var o = n.repeat && s.TweenMax || f;
            return e ? this.add(o.fromTo(t, e, i, n), r) : this.set(t, n, r)
        }
        ,
        i.staggerTo = function(t, e, i, n, r, o, s, a) {
            var l, u, c = new d({
                onComplete: o,
                onCompleteParams: s,
                callbackScope: a,
                smoothChildTiming: this.smoothChildTiming
            }), h = i.cycle;
            for ("string" == typeof t && (t = f.selector(t) || t),
            p(t = t || []) && (t = w(t)),
            (n = n || 0) < 0 && ((t = w(t)).reverse(),
            n *= -1),
            u = 0; u < t.length; u++)
                (l = v(i)).startAt && (l.startAt = v(l.startAt),
                l.startAt.cycle && y(l.startAt, t, u)),
                h && (y(l, t, u),
                null != l.duration && (e = l.duration,
                delete l.duration)),
                c.to(t[u], e, l, u * n);
            return this.add(c, r)
        }
        ,
        i.staggerFrom = function(t, e, i, n, r, o, s, a) {
            return i.immediateRender = 0 != i.immediateRender,
            i.runBackwards = !0,
            this.staggerTo(t, e, i, n, r, o, s, a)
        }
        ,
        i.staggerFromTo = function(t, e, i, n, r, o, s, a, l) {
            return n.startAt = i,
            n.immediateRender = 0 != n.immediateRender && 0 != i.immediateRender,
            this.staggerTo(t, e, n, r, o, s, a, l)
        }
        ,
        i.call = function(t, e, i, n) {
            return this.add(f.delayedCall(0, t, e, i), n)
        }
        ,
        i.set = function(t, e, i) {
            return i = this._parseTimeOrLabel(i, 0, !0),
            null == e.immediateRender && (e.immediateRender = i === this._time && !this._paused),
            this.add(new f(t,0,e), i)
        }
        ,
        d.exportRoot = function(t, e) {
            null == (t = t || {}).smoothChildTiming && (t.smoothChildTiming = !0);
            var i, n, r, o, s = new d(t), a = s._timeline;
            for (null == e && (e = !0),
            a._remove(s, !0),
            s._startTime = 0,
            s._rawPrevTime = s._time = s._totalTime = a._time,
            r = a._first; r; )
                o = r._next,
                e && r instanceof f && r.target === r.vars.onComplete || ((n = r._startTime - r._delay) < 0 && (i = 1),
                s.add(r, n)),
                r = o;
            return a.add(s, 0),
            i && s.totalDuration(),
            s
        }
        ,
        i.add = function(t, e, i, n) {
            var r, o, s, a, l, u;
            if ("number" != typeof e && (e = this._parseTimeOrLabel(e, 0, !0, t)),
            !(t instanceof c)) {
                if (t instanceof Array || t && t.push && g(t)) {
                    for (i = i || "normal",
                    n = n || 0,
                    r = e,
                    o = t.length,
                    s = 0; s < o; s++)
                        g(a = t[s]) && (a = new d({
                            tweens: a
                        })),
                        this.add(a, r),
                        "string" != typeof a && "function" != typeof a && ("sequence" === i ? r = a._startTime + a.totalDuration() / a._timeScale : "start" === i && (a._startTime -= a.delay())),
                        r += n;
                    return this._uncache(!0)
                }
                if ("string" == typeof t)
                    return this.addLabel(t, e);
                if ("function" != typeof t)
                    throw "Cannot add " + t + " into the timeline; it is not a tween, timeline, function, or string.";
                t = f.delayedCall(0, t)
            }
            if (h.prototype.add.call(this, t, e),
            t._time && (r = Math.max(0, Math.min(t.totalDuration(), (this.rawTime() - t._startTime) * t._timeScale)),
            1e-5 < Math.abs(r - t._totalTime) && t.render(r, !1, !1)),
            (this._gc || this._time === this._duration) && !this._paused && this._duration < this.duration())
                for (u = (l = this).rawTime() > t._startTime; l._timeline; )
                    u && l._timeline.smoothChildTiming ? l.totalTime(l._totalTime, !0) : l._gc && l._enabled(!0, !1),
                    l = l._timeline;
            return this
        }
        ,
        i.remove = function(t) {
            if (t instanceof c) {
                this._remove(t, !1);
                var e = t._timeline = t.vars.useFrames ? c._rootFramesTimeline : c._rootTimeline;
                return t._startTime = (t._paused ? t._pauseTime : e._time) - (t._reversed ? t.totalDuration() - t._totalTime : t._totalTime) / t._timeScale,
                this
            }
            if (t instanceof Array || t && t.push && g(t)) {
                for (var i = t.length; -1 < --i; )
                    this.remove(t[i]);
                return this
            }
            return "string" == typeof t ? this.removeLabel(t) : this.kill(null, t)
        }
        ,
        i._remove = function(t, e) {
            return h.prototype._remove.call(this, t, e),
            this._last ? this._time > this.duration() && (this._time = this._duration,
            this._totalTime = this._totalDuration) : this._time = this._totalTime = this._duration = this._totalDuration = 0,
            this
        }
        ,
        i.append = function(t, e) {
            return this.add(t, this._parseTimeOrLabel(null, e, !0, t))
        }
        ,
        i.insert = i.insertMultiple = function(t, e, i, n) {
            return this.add(t, e || 0, i, n)
        }
        ,
        i.appendMultiple = function(t, e, i, n) {
            return this.add(t, this._parseTimeOrLabel(null, e, !0, t), i, n)
        }
        ,
        i.addLabel = function(t, e) {
            return this._labels[t] = this._parseTimeOrLabel(e),
            this
        }
        ,
        i.addPause = function(t, e, i, n) {
            var r = f.delayedCall(0, o, i, n || this);
            return r.vars.onComplete = r.vars.onReverseComplete = e,
            r.data = "isPause",
            this._hasPause = !0,
            this.add(r, t)
        }
        ,
        i.removeLabel = function(t) {
            return delete this._labels[t],
            this
        }
        ,
        i.getLabelTime = function(t) {
            return null != this._labels[t] ? this._labels[t] : -1
        }
        ,
        i._parseTimeOrLabel = function(t, e, i, n) {
            var r, o;
            if (n instanceof c && n.timeline === this)
                this.remove(n);
            else if (n && (n instanceof Array || n.push && g(n)))
                for (o = n.length; -1 < --o; )
                    n[o]instanceof c && n[o].timeline === this && this.remove(n[o]);
            if (r = "number" != typeof t || e ? 99999999999 < this.duration() ? this.recent().endTime(!1) : this._duration : 0,
            "string" == typeof e)
                return this._parseTimeOrLabel(e, i && "number" == typeof t && null == this._labels[e] ? t - r : 0, i);
            if (e = e || 0,
            "string" != typeof t || !isNaN(t) && null == this._labels[t])
                null == t && (t = r);
            else {
                if (-1 === (o = t.indexOf("=")))
                    return null == this._labels[t] ? i ? this._labels[t] = r + e : e : this._labels[t] + e;
                e = parseInt(t.charAt(o - 1) + "1", 10) * Number(t.substr(o + 1)),
                t = 1 < o ? this._parseTimeOrLabel(t.substr(0, o - 1), 0, i) : r
            }
            return Number(t) + e
        }
        ,
        i.seek = function(t, e) {
            return this.totalTime("number" == typeof t ? t : this._parseTimeOrLabel(t), !1 !== e)
        }
        ,
        i.stop = function() {
            return this.paused(!0)
        }
        ,
        i.gotoAndPlay = function(t, e) {
            return this.play(t, e)
        }
        ,
        i.gotoAndStop = function(t, e) {
            return this.pause(t, e)
        }
        ,
        i.render = function(t, e, i) {
            this._gc && this._enabled(!0, !1);
            var n, r, o, s, a, l, u, c = this._time, h = this._dirty ? this.totalDuration() : this._totalDuration, f = this._startTime, d = this._timeScale, p = this._paused;
            if (c !== this._time && (t += this._time - c),
            h - 1e-7 <= t && 0 <= t)
                this._totalTime = this._time = h,
                this._reversed || this._hasPausedChild() || (r = !0,
                s = "onComplete",
                a = !!this._timeline.autoRemoveChildren,
                0 === this._duration && (t <= 0 && -1e-7 <= t || this._rawPrevTime < 0 || 1e-10 === this._rawPrevTime) && this._rawPrevTime !== t && this._first && (a = !0,
                1e-10 < this._rawPrevTime && (s = "onReverseComplete"))),
                this._rawPrevTime = this._duration || !e || t || this._rawPrevTime === t ? t : 1e-10,
                t = h + 1e-4;
            else if (t < 1e-7)
                if (this._totalTime = this._time = 0,
                (0 !== c || 0 === this._duration && 1e-10 !== this._rawPrevTime && (0 < this._rawPrevTime || t < 0 && 0 <= this._rawPrevTime)) && (s = "onReverseComplete",
                r = this._reversed),
                t < 0)
                    this._active = !1,
                    this._timeline.autoRemoveChildren && this._reversed ? (a = r = !0,
                    s = "onReverseComplete") : 0 <= this._rawPrevTime && this._first && (a = !0),
                    this._rawPrevTime = t;
                else {
                    if (this._rawPrevTime = this._duration || !e || t || this._rawPrevTime === t ? t : 1e-10,
                    0 === t && r)
                        for (n = this._first; n && 0 === n._startTime; )
                            n._duration || (r = !1),
                            n = n._next;
                    t = 0,
                    this._initted || (a = !0)
                }
            else {
                if (this._hasPause && !this._forcingPlayhead && !e) {
                    if (c <= t)
                        for (n = this._first; n && n._startTime <= t && !l; )
                            n._duration || "isPause" !== n.data || n.ratio || 0 === n._startTime && 0 === this._rawPrevTime || (l = n),
                            n = n._next;
                    else
                        for (n = this._last; n && n._startTime >= t && !l; )
                            n._duration || "isPause" === n.data && 0 < n._rawPrevTime && (l = n),
                            n = n._prev;
                    l && (this._time = t = l._startTime,
                    this._totalTime = t + this._cycle * (this._totalDuration + this._repeatDelay))
                }
                this._totalTime = this._time = this._rawPrevTime = t
            }
            if (this._time !== c && this._first || i || a || l) {
                if (this._initted || (this._initted = !0),
                this._active || !this._paused && this._time !== c && 0 < t && (this._active = !0),
                0 === c && this.vars.onStart && (0 === this._time && this._duration || e || this._callback("onStart")),
                c <= (u = this._time))
                    for (n = this._first; n && (o = n._next,
                    u === this._time && (!this._paused || p)); )
                        (n._active || n._startTime <= u && !n._paused && !n._gc) && (l === n && this.pause(),
                        n._reversed ? n.render((n._dirty ? n.totalDuration() : n._totalDuration) - (t - n._startTime) * n._timeScale, e, i) : n.render((t - n._startTime) * n._timeScale, e, i)),
                        n = o;
                else
                    for (n = this._last; n && (o = n._prev,
                    u === this._time && (!this._paused || p)); ) {
                        if (n._active || n._startTime <= c && !n._paused && !n._gc) {
                            if (l === n) {
                                for (l = n._prev; l && l.endTime() > this._time; )
                                    l.render(l._reversed ? l.totalDuration() - (t - l._startTime) * l._timeScale : (t - l._startTime) * l._timeScale, e, i),
                                    l = l._prev;
                                l = null,
                                this.pause()
                            }
                            n._reversed ? n.render((n._dirty ? n.totalDuration() : n._totalDuration) - (t - n._startTime) * n._timeScale, e, i) : n.render((t - n._startTime) * n._timeScale, e, i)
                        }
                        n = o
                    }
                this._onUpdate && (e || (_.length && m(),
                this._callback("onUpdate"))),
                s && (this._gc || (f === this._startTime || d !== this._timeScale) && (0 === this._time || h >= this.totalDuration()) && (r && (_.length && m(),
                this._timeline.autoRemoveChildren && this._enabled(!1, !1),
                this._active = !1),
                !e && this.vars[s] && this._callback(s)))
            }
        }
        ,
        i._hasPausedChild = function() {
            for (var t = this._first; t; ) {
                if (t._paused || t instanceof d && t._hasPausedChild())
                    return !0;
                t = t._next
            }
            return !1
        }
        ,
        i.getChildren = function(t, e, i, n) {
            n = n || -9999999999;
            for (var r = [], o = this._first, s = 0; o; )
                o._startTime < n || (o instanceof f ? !1 !== e && (r[s++] = o) : (!1 !== i && (r[s++] = o),
                !1 !== t && (s = (r = r.concat(o.getChildren(!0, e, i))).length))),
                o = o._next;
            return r
        }
        ,
        i.getTweensOf = function(t, e) {
            var i, n, r = this._gc, o = [], s = 0;
            for (r && this._enabled(!0, !0),
            n = (i = f.getTweensOf(t)).length; -1 < --n; )
                (i[n].timeline === this || e && this._contains(i[n])) && (o[s++] = i[n]);
            return r && this._enabled(!1, !0),
            o
        }
        ,
        i.recent = function() {
            return this._recent
        }
        ,
        i._contains = function(t) {
            for (var e = t.timeline; e; ) {
                if (e === this)
                    return !0;
                e = e.timeline
            }
            return !1
        }
        ,
        i.shiftChildren = function(t, e, i) {
            i = i || 0;
            for (var n, r = this._first, o = this._labels; r; )
                r._startTime >= i && (r._startTime += t),
                r = r._next;
            if (e)
                for (n in o)
                    o[n] >= i && (o[n] += t);
            return this._uncache(!0)
        }
        ,
        i._kill = function(t, e) {
            if (!t && !e)
                return this._enabled(!1, !1);
            for (var i = e ? this.getTweensOf(e) : this.getChildren(!0, !0, !1), n = i.length, r = !1; -1 < --n; )
                i[n]._kill(t, e) && (r = !0);
            return r
        }
        ,
        i.clear = function(t) {
            var e = this.getChildren(!1, !0, !0)
              , i = e.length;
            for (this._time = this._totalTime = 0; -1 < --i; )
                e[i]._enabled(!1, !1);
            return !1 !== t && (this._labels = {}),
            this._uncache(!0)
        }
        ,
        i.invalidate = function() {
            for (var t = this._first; t; )
                t.invalidate(),
                t = t._next;
            return c.prototype.invalidate.call(this)
        }
        ,
        i._enabled = function(t, e) {
            if (t === this._gc)
                for (var i = this._first; i; )
                    i._enabled(t, !0),
                    i = i._next;
            return h.prototype._enabled.call(this, t, e)
        }
        ,
        i.totalTime = function(t, e, i) {
            this._forcingPlayhead = !0;
            var n = c.prototype.totalTime.apply(this, arguments);
            return this._forcingPlayhead = !1,
            n
        }
        ,
        i.duration = function(t) {
            return arguments.length ? (0 !== this.duration() && 0 !== t && this.timeScale(this._duration / t),
            this) : (this._dirty && this.totalDuration(),
            this._duration)
        }
        ,
        i.totalDuration = function(t) {
            if (arguments.length)
                return t && this.totalDuration() ? this.timeScale(this._totalDuration / t) : this;
            if (this._dirty) {
                for (var e, i, n = 0, r = this._last, o = 999999999999; r; )
                    e = r._prev,
                    r._dirty && r.totalDuration(),
                    r._startTime > o && this._sortChildren && !r._paused && !this._calculatingDuration ? (this._calculatingDuration = 1,
                    this.add(r, r._startTime - r._delay),
                    this._calculatingDuration = 0) : o = r._startTime,
                    r._startTime < 0 && !r._paused && (n -= r._startTime,
                    this._timeline.smoothChildTiming && (this._startTime += r._startTime / this._timeScale,
                    this._time -= r._startTime,
                    this._totalTime -= r._startTime,
                    this._rawPrevTime -= r._startTime),
                    this.shiftChildren(-r._startTime, !1, -9999999999),
                    o = 0),
                    n < (i = r._startTime + r._totalDuration / r._timeScale) && (n = i),
                    r = e;
                this._duration = this._totalDuration = n,
                this._dirty = !1
            }
            return this._totalDuration
        }
        ,
        i.paused = function(t) {
            if (!t)
                for (var e = this._first, i = this._time; e; )
                    e._startTime === i && "isPause" === e.data && (e._rawPrevTime = 0),
                    e = e._next;
            return c.prototype.paused.apply(this, arguments)
        }
        ,
        i.usesFrames = function() {
            for (var t = this._timeline; t._timeline; )
                t = t._timeline;
            return t === c._rootFramesTimeline
        }
        ,
        i.rawTime = function(t) {
            return t && (this._paused || this._repeat && 0 < this.time() && this.totalProgress() < 1) ? this._totalTime % (this._duration + this._repeatDelay) : this._paused ? this._totalTime : (this._timeline.rawTime(t) - this._startTime) * this._timeScale
        }
        ,
        d
    }, !0),
    _gsScope._gsDefine("TimelineMax", ["TimelineLite", "TweenLite", "easing.Ease"], function(e, a, t) {
        var i = function(t) {
            e.call(this, t),
            this._repeat = this.vars.repeat || 0,
            this._repeatDelay = this.vars.repeatDelay || 0,
            this._cycle = 0,
            this._yoyo = !0 === this.vars.yoyo,
            this._dirty = !0
        }
          , n = a._internals
          , S = n.lazyTweens
          , $ = n.lazyRender
          , l = _gsScope._gsDefine.globals
          , u = new t(null,null,1,0)
          , r = i.prototype = new e;
        return r.constructor = i,
        r.kill()._gc = !1,
        i.version = "2.0.2",
        r.invalidate = function() {
            return this._yoyo = !0 === this.vars.yoyo,
            this._repeat = this.vars.repeat || 0,
            this._repeatDelay = this.vars.repeatDelay || 0,
            this._uncache(!0),
            e.prototype.invalidate.call(this)
        }
        ,
        r.addCallback = function(t, e, i, n) {
            return this.add(a.delayedCall(0, t, i, n), e)
        }
        ,
        r.removeCallback = function(t, e) {
            if (t)
                if (null == e)
                    this._kill(null, t);
                else
                    for (var i = this.getTweensOf(t, !1), n = i.length, r = this._parseTimeOrLabel(e); -1 < --n; )
                        i[n]._startTime === r && i[n]._enabled(!1, !1);
            return this
        }
        ,
        r.removePause = function(t) {
            return this.removeCallback(e._internals.pauseCallback, t)
        }
        ,
        r.tweenTo = function(t, e) {
            e = e || {};
            var i, n, r, o = {
                ease: u,
                useFrames: this.usesFrames(),
                immediateRender: !1,
                lazy: !1
            }, s = e.repeat && l.TweenMax || a;
            for (n in e)
                o[n] = e[n];
            return o.time = this._parseTimeOrLabel(t),
            i = Math.abs(Number(o.time) - this._time) / this._timeScale || .001,
            r = new s(this,i,o),
            o.onStart = function() {
                r.target.paused(!0),
                r.vars.time === r.target.time() || i !== r.duration() || r.isFromTo || r.duration(Math.abs(r.vars.time - r.target.time()) / r.target._timeScale).render(r.time(), !0, !0),
                e.onStart && e.onStart.apply(e.onStartScope || e.callbackScope || r, e.onStartParams || [])
            }
            ,
            r
        }
        ,
        r.tweenFromTo = function(t, e, i) {
            i = i || {},
            t = this._parseTimeOrLabel(t),
            i.startAt = {
                onComplete: this.seek,
                onCompleteParams: [t],
                callbackScope: this
            },
            i.immediateRender = !1 !== i.immediateRender;
            var n = this.tweenTo(e, i);
            return n.isFromTo = 1,
            n.duration(Math.abs(n.vars.time - t) / this._timeScale || .001)
        }
        ,
        r.render = function(t, e, i) {
            this._gc && this._enabled(!0, !1);
            var n, r, o, s, a, l, u, c, h = this._time, f = this._dirty ? this.totalDuration() : this._totalDuration, d = this._duration, p = this._totalTime, g = this._startTime, _ = this._timeScale, m = this._rawPrevTime, v = this._paused, y = this._cycle;
            if (h !== this._time && (t += this._time - h),
            f - 1e-7 <= t && 0 <= t)
                this._locked || (this._totalTime = f,
                this._cycle = this._repeat),
                this._reversed || this._hasPausedChild() || (r = !0,
                s = "onComplete",
                a = !!this._timeline.autoRemoveChildren,
                0 === this._duration && (t <= 0 && -1e-7 <= t || m < 0 || 1e-10 === m) && m !== t && this._first && (a = !0,
                1e-10 < m && (s = "onReverseComplete"))),
                this._rawPrevTime = this._duration || !e || t || this._rawPrevTime === t ? t : 1e-10,
                this._yoyo && 0 != (1 & this._cycle) ? this._time = t = 0 : t = (this._time = d) + 1e-4;
            else if (t < 1e-7)
                if (this._locked || (this._totalTime = this._cycle = 0),
                ((this._time = 0) !== h || 0 === d && 1e-10 !== m && (0 < m || t < 0 && 0 <= m) && !this._locked) && (s = "onReverseComplete",
                r = this._reversed),
                t < 0)
                    this._active = !1,
                    this._timeline.autoRemoveChildren && this._reversed ? (a = r = !0,
                    s = "onReverseComplete") : 0 <= m && this._first && (a = !0),
                    this._rawPrevTime = t;
                else {
                    if (this._rawPrevTime = d || !e || t || this._rawPrevTime === t ? t : 1e-10,
                    0 === t && r)
                        for (n = this._first; n && 0 === n._startTime; )
                            n._duration || (r = !1),
                            n = n._next;
                    t = 0,
                    this._initted || (a = !0)
                }
            else if (0 === d && m < 0 && (a = !0),
            this._time = this._rawPrevTime = t,
            this._locked || (this._totalTime = t,
            0 !== this._repeat && (l = d + this._repeatDelay,
            this._cycle = this._totalTime / l >> 0,
            0 !== this._cycle && this._cycle === this._totalTime / l && p <= t && this._cycle--,
            this._time = this._totalTime - this._cycle * l,
            this._yoyo && 0 != (1 & this._cycle) && (this._time = d - this._time),
            this._time > d ? t = (this._time = d) + 1e-4 : this._time < 0 ? this._time = t = 0 : t = this._time)),
            this._hasPause && !this._forcingPlayhead && !e) {
                if (h <= (t = this._time) || this._repeat && y !== this._cycle)
                    for (n = this._first; n && n._startTime <= t && !u; )
                        n._duration || "isPause" !== n.data || n.ratio || 0 === n._startTime && 0 === this._rawPrevTime || (u = n),
                        n = n._next;
                else
                    for (n = this._last; n && n._startTime >= t && !u; )
                        n._duration || "isPause" === n.data && 0 < n._rawPrevTime && (u = n),
                        n = n._prev;
                u && u._startTime < d && (this._time = t = u._startTime,
                this._totalTime = t + this._cycle * (this._totalDuration + this._repeatDelay))
            }
            if (this._cycle !== y && !this._locked) {
                var w = this._yoyo && 0 != (1 & y)
                  , b = w === (this._yoyo && 0 != (1 & this._cycle))
                  , x = this._totalTime
                  , T = this._cycle
                  , C = this._rawPrevTime
                  , k = this._time;
                if (this._totalTime = y * d,
                this._cycle < y ? w = !w : this._totalTime += d,
                this._time = h,
                this._rawPrevTime = 0 === d ? m - 1e-4 : m,
                this._cycle = y,
                this._locked = !0,
                h = w ? 0 : d,
                this.render(h, e, 0 === d),
                e || this._gc || this.vars.onRepeat && (this._cycle = T,
                this._locked = !1,
                this._callback("onRepeat")),
                h !== this._time)
                    return;
                if (b && (this._cycle = y,
                this._locked = !0,
                h = w ? d + 1e-4 : -1e-4,
                this.render(h, !0, !1)),
                this._locked = !1,
                this._paused && !v)
                    return;
                this._time = k,
                this._totalTime = x,
                this._cycle = T,
                this._rawPrevTime = C
            }
            if (this._time !== h && this._first || i || a || u) {
                if (this._initted || (this._initted = !0),
                this._active || !this._paused && this._totalTime !== p && 0 < t && (this._active = !0),
                0 === p && this.vars.onStart && (0 === this._totalTime && this._totalDuration || e || this._callback("onStart")),
                h <= (c = this._time))
                    for (n = this._first; n && (o = n._next,
                    c === this._time && (!this._paused || v)); )
                        (n._active || n._startTime <= this._time && !n._paused && !n._gc) && (u === n && this.pause(),
                        n._reversed ? n.render((n._dirty ? n.totalDuration() : n._totalDuration) - (t - n._startTime) * n._timeScale, e, i) : n.render((t - n._startTime) * n._timeScale, e, i)),
                        n = o;
                else
                    for (n = this._last; n && (o = n._prev,
                    c === this._time && (!this._paused || v)); ) {
                        if (n._active || n._startTime <= h && !n._paused && !n._gc) {
                            if (u === n) {
                                for (u = n._prev; u && u.endTime() > this._time; )
                                    u.render(u._reversed ? u.totalDuration() - (t - u._startTime) * u._timeScale : (t - u._startTime) * u._timeScale, e, i),
                                    u = u._prev;
                                u = null,
                                this.pause()
                            }
                            n._reversed ? n.render((n._dirty ? n.totalDuration() : n._totalDuration) - (t - n._startTime) * n._timeScale, e, i) : n.render((t - n._startTime) * n._timeScale, e, i)
                        }
                        n = o
                    }
                this._onUpdate && (e || (S.length && $(),
                this._callback("onUpdate"))),
                s && (this._locked || this._gc || (g === this._startTime || _ !== this._timeScale) && (0 === this._time || f >= this.totalDuration()) && (r && (S.length && $(),
                this._timeline.autoRemoveChildren && this._enabled(!1, !1),
                this._active = !1),
                !e && this.vars[s] && this._callback(s)))
            } else
                p !== this._totalTime && this._onUpdate && (e || this._callback("onUpdate"))
        }
        ,
        r.getActive = function(t, e, i) {
            null == t && (t = !0),
            null == e && (e = !0),
            null == i && (i = !1);
            var n, r, o = [], s = this.getChildren(t, e, i), a = 0, l = s.length;
            for (n = 0; n < l; n++)
                (r = s[n]).isActive() && (o[a++] = r);
            return o
        }
        ,
        r.getLabelAfter = function(t) {
            t || 0 !== t && (t = this._time);
            var e, i = this.getLabelsArray(), n = i.length;
            for (e = 0; e < n; e++)
                if (i[e].time > t)
                    return i[e].name;
            return null
        }
        ,
        r.getLabelBefore = function(t) {
            null == t && (t = this._time);
            for (var e = this.getLabelsArray(), i = e.length; -1 < --i; )
                if (e[i].time < t)
                    return e[i].name;
            return null
        }
        ,
        r.getLabelsArray = function() {
            var t, e = [], i = 0;
            for (t in this._labels)
                e[i++] = {
                    time: this._labels[t],
                    name: t
                };
            return e.sort(function(t, e) {
                return t.time - e.time
            }),
            e
        }
        ,
        r.invalidate = function() {
            return this._locked = !1,
            e.prototype.invalidate.call(this)
        }
        ,
        r.progress = function(t, e) {
            return arguments.length ? this.totalTime(this.duration() * (this._yoyo && 0 != (1 & this._cycle) ? 1 - t : t) + this._cycle * (this._duration + this._repeatDelay), e) : this._time / this.duration() || 0
        }
        ,
        r.totalProgress = function(t, e) {
            return arguments.length ? this.totalTime(this.totalDuration() * t, e) : this._totalTime / this.totalDuration() || 0
        }
        ,
        r.totalDuration = function(t) {
            return arguments.length ? -1 !== this._repeat && t ? this.timeScale(this.totalDuration() / t) : this : (this._dirty && (e.prototype.totalDuration.call(this),
            this._totalDuration = -1 === this._repeat ? 999999999999 : this._duration * (this._repeat + 1) + this._repeatDelay * this._repeat),
            this._totalDuration)
        }
        ,
        r.time = function(t, e) {
            return arguments.length ? (this._dirty && this.totalDuration(),
            t > this._duration && (t = this._duration),
            this._yoyo && 0 != (1 & this._cycle) ? t = this._duration - t + this._cycle * (this._duration + this._repeatDelay) : 0 !== this._repeat && (t += this._cycle * (this._duration + this._repeatDelay)),
            this.totalTime(t, e)) : this._time
        }
        ,
        r.repeat = function(t) {
            return arguments.length ? (this._repeat = t,
            this._uncache(!0)) : this._repeat
        }
        ,
        r.repeatDelay = function(t) {
            return arguments.length ? (this._repeatDelay = t,
            this._uncache(!0)) : this._repeatDelay
        }
        ,
        r.yoyo = function(t) {
            return arguments.length ? (this._yoyo = t,
            this) : this._yoyo
        }
        ,
        r.currentLabel = function(t) {
            return arguments.length ? this.seek(t, !0) : this.getLabelBefore(this._time + 1e-8)
        }
        ,
        i
    }, !0),
    T = 180 / Math.PI,
    b = [],
    x = [],
    C = [],
    m = {},
    i = _gsScope._gsDefine.globals,
    v = function(t, e, i, n) {
        i === n && (i = n - (n - e) / 1e6),
        t === e && (e = t + (i - t) / 1e6),
        this.a = t,
        this.b = e,
        this.c = i,
        this.d = n,
        this.da = n - t,
        this.ca = i - t,
        this.ba = e - t
    }
    ,
    k = function(t, e, i, n) {
        var r = {
            a: t
        }
          , o = {}
          , s = {}
          , a = {
            c: n
        }
          , l = (t + e) / 2
          , u = (e + i) / 2
          , c = (i + n) / 2
          , h = (l + u) / 2
          , f = (u + c) / 2
          , d = (f - h) / 8;
        return r.b = l + (t - l) / 4,
        o.b = h + d,
        r.c = o.a = (r.b + o.b) / 2,
        o.c = s.a = (h + f) / 2,
        s.b = f - d,
        a.b = c + (n - c) / 4,
        s.c = a.a = (s.b + a.b) / 2,
        [r, o, s, a]
    }
    ,
    y = function(t, e, i, n, r) {
        var o, s, a, l, u, c, h, f, d, p, g, _, m, v = t.length - 1, y = 0, w = t[0].a;
        for (o = 0; o < v; o++)
            s = (u = t[y]).a,
            a = u.d,
            l = t[y + 1].d,
            f = r ? (g = b[o],
            m = ((_ = x[o]) + g) * e * .25 / (n ? .5 : C[o] || .5),
            a - ((c = a - (a - s) * (n ? .5 * e : 0 !== g ? m / g : 0)) + (((h = a + (l - a) * (n ? .5 * e : 0 !== _ ? m / _ : 0)) - c) * (3 * g / (g + _) + .5) / 4 || 0))) : a - ((c = a - (a - s) * e * .5) + (h = a + (l - a) * e * .5)) / 2,
            c += f,
            h += f,
            u.c = d = c,
            u.b = 0 !== o ? w : w = u.a + .6 * (u.c - u.a),
            u.da = a - s,
            u.ca = d - s,
            u.ba = w - s,
            i ? (p = k(s, w, d, a),
            t.splice(y, 1, p[0], p[1], p[2], p[3]),
            y += 4) : y++,
            w = h;
        (u = t[y]).b = w,
        u.c = w + .4 * (u.d - w),
        u.da = u.d - u.a,
        u.ca = u.c - u.a,
        u.ba = w - u.a,
        i && (p = k(u.a, w, u.c, u.d),
        t.splice(y, 1, p[0], p[1], p[2], p[3]))
    }
    ,
    w = function(t, e, i, n) {
        var r, o, s, a, l, u, c = [];
        if (n)
            for (o = (t = [n].concat(t)).length; -1 < --o; )
                "string" == typeof (u = t[o][e]) && "=" === u.charAt(1) && (t[o][e] = n[e] + Number(u.charAt(0) + u.substr(2)));
        if ((r = t.length - 2) < 0)
            return c[0] = new v(t[0][e],0,0,t[0][e]),
            c;
        for (o = 0; o < r; o++)
            s = t[o][e],
            a = t[o + 1][e],
            c[o] = new v(s,0,0,a),
            i && (l = t[o + 2][e],
            b[o] = (b[o] || 0) + (a - s) * (a - s),
            x[o] = (x[o] || 0) + (l - a) * (l - a));
        return c[o] = new v(t[o][e],0,0,t[o + 1][e]),
        c
    }
    ,
    d = function(t, e, i, n, r, o) {
        var s, a, l, u, c, h, f, d, p = {}, g = [], _ = o || t[0];
        for (a in r = "string" == typeof r ? "," + r + "," : ",x,y,z,left,top,right,bottom,marginTop,marginLeft,marginRight,marginBottom,paddingLeft,paddingTop,paddingRight,paddingBottom,backgroundPosition,backgroundPosition_y,",
        null == e && (e = 1),
        t[0])
            g.push(a);
        if (1 < t.length) {
            for (d = t[t.length - 1],
            f = !0,
            s = g.length; -1 < --s; )
                if (a = g[s],
                .05 < Math.abs(_[a] - d[a])) {
                    f = !1;
                    break
                }
            f && (t = t.concat(),
            o && t.unshift(o),
            t.push(t[1]),
            o = t[t.length - 3])
        }
        for (b.length = x.length = C.length = 0,
        s = g.length; -1 < --s; )
            a = g[s],
            m[a] = -1 !== r.indexOf("," + a + ","),
            p[a] = w(t, a, m[a], o);
        for (s = b.length; -1 < --s; )
            b[s] = Math.sqrt(b[s]),
            x[s] = Math.sqrt(x[s]);
        if (!n) {
            for (s = g.length; -1 < --s; )
                if (m[a])
                    for (h = (l = p[g[s]]).length - 1,
                    u = 0; u < h; u++)
                        c = l[u + 1].da / x[u] + l[u].da / b[u] || 0,
                        C[u] = (C[u] || 0) + c * c;
            for (s = C.length; -1 < --s; )
                C[s] = Math.sqrt(C[s])
        }
        for (s = g.length,
        u = i ? 4 : 1; -1 < --s; )
            l = p[a = g[s]],
            y(l, e, i, n, m[a]),
            f && (l.splice(0, u),
            l.splice(l.length - u, u));
        return p
    }
    ,
    p = function(t, e, i) {
        for (var n, r, o, s, a, l, u, c, h, f, d, p = 1 / i, g = t.length; -1 < --g; )
            for (o = (f = t[g]).a,
            s = f.d - o,
            a = f.c - o,
            l = f.b - o,
            n = r = 0,
            c = 1; c <= i; c++)
                n = r - (r = ((u = p * c) * u * s + 3 * (h = 1 - u) * (u * a + h * l)) * u),
                e[d = g * i + c - 1] = (e[d] || 0) + n * n
    }
    ,
    _ = _gsScope._gsDefine.plugin({
        propName: "bezier",
        priority: -1,
        version: "1.3.8",
        API: 2,
        global: !0,
        init: function(t, e, i) {
            this._target = t,
            e instanceof Array && (e = {
                values: e
            }),
            this._func = {},
            this._mod = {},
            this._props = [],
            this._timeRes = null == e.timeResolution ? 6 : parseInt(e.timeResolution, 10);
            var n, r, o, s, a, l = e.values || [], u = {}, c = l[0], h = e.autoRotate || i.vars.orientToBezier;
            for (n in this._autoRotate = h ? h instanceof Array ? h : [["x", "y", "rotation", !0 === h ? 0 : Number(h) || 0]] : null,
            c)
                this._props.push(n);
            for (o = this._props.length; -1 < --o; )
                n = this._props[o],
                this._overwriteProps.push(n),
                r = this._func[n] = "function" == typeof t[n],
                u[n] = r ? t[n.indexOf("set") || "function" != typeof t["get" + n.substr(3)] ? n : "get" + n.substr(3)]() : parseFloat(t[n]),
                a || u[n] !== l[0][n] && (a = u);
            if (this._beziers = "cubic" !== e.type && "quadratic" !== e.type && "soft" !== e.type ? d(l, isNaN(e.curviness) ? 1 : e.curviness, !1, "thruBasic" === e.type, e.correlate, a) : function(t, e, i) {
                var n, r, o, s, a, l, u, c, h, f, d, p = {}, g = "cubic" === (e = e || "soft") ? 3 : 2, _ = "soft" === e, m = [];
                if (_ && i && (t = [i].concat(t)),
                null == t || t.length < g + 1)
                    throw "invalid Bezier data";
                for (h in t[0])
                    m.push(h);
                for (l = m.length; -1 < --l; ) {
                    for (p[h = m[l]] = a = [],
                    f = 0,
                    c = t.length,
                    u = 0; u < c; u++)
                        n = null == i ? t[u][h] : "string" == typeof (d = t[u][h]) && "=" === d.charAt(1) ? i[h] + Number(d.charAt(0) + d.substr(2)) : Number(d),
                        _ && 1 < u && u < c - 1 && (a[f++] = (n + a[f - 2]) / 2),
                        a[f++] = n;
                    for (c = f - g + 1,
                    u = f = 0; u < c; u += g)
                        n = a[u],
                        r = a[u + 1],
                        o = a[u + 2],
                        s = 2 === g ? 0 : a[u + 3],
                        a[f++] = d = 3 === g ? new v(n,r,o,s) : new v(n,(2 * r + n) / 3,(2 * r + o) / 3,o);
                    a.length = f
                }
                return p
            }(l, e.type, u),
            this._segCount = this._beziers[n].length,
            this._timeRes) {
                var f = function(t, e) {
                    var i, n, r, o, s = [], a = [], l = 0, u = 0, c = (e = e >> 0 || 6) - 1, h = [], f = [];
                    for (i in t)
                        p(t[i], s, e);
                    for (r = s.length,
                    n = 0; n < r; n++)
                        l += Math.sqrt(s[n]),
                        f[o = n % e] = l,
                        o === c && (u += l,
                        h[o = n / e >> 0] = f,
                        a[o] = u,
                        l = 0,
                        f = []);
                    return {
                        length: u,
                        lengths: a,
                        segments: h
                    }
                }(this._beziers, this._timeRes);
                this._length = f.length,
                this._lengths = f.lengths,
                this._segments = f.segments,
                this._l1 = this._li = this._s1 = this._si = 0,
                this._l2 = this._lengths[0],
                this._curSeg = this._segments[0],
                this._s2 = this._curSeg[0],
                this._prec = 1 / this._curSeg.length
            }
            if (h = this._autoRotate)
                for (this._initialRotations = [],
                h[0]instanceof Array || (this._autoRotate = h = [h]),
                o = h.length; -1 < --o; ) {
                    for (s = 0; s < 3; s++)
                        n = h[o][s],
                        this._func[n] = "function" == typeof t[n] && t[n.indexOf("set") || "function" != typeof t["get" + n.substr(3)] ? n : "get" + n.substr(3)];
                    n = h[o][2],
                    this._initialRotations[o] = (this._func[n] ? this._func[n].call(this._target) : this._target[n]) || 0,
                    this._overwriteProps.push(n)
                }
            return this._startRatio = i.vars.runBackwards ? 1 : 0,
            !0
        },
        set: function(t) {
            var e, i, n, r, o, s, a, l, u, c, h = this._segCount, f = this._func, d = this._target, p = t !== this._startRatio;
            if (this._timeRes) {
                if (u = this._lengths,
                c = this._curSeg,
                t *= this._length,
                n = this._li,
                t > this._l2 && n < h - 1) {
                    for (l = h - 1; n < l && (this._l2 = u[++n]) <= t; )
                        ;
                    this._l1 = u[n - 1],
                    this._li = n,
                    this._curSeg = c = this._segments[n],
                    this._s2 = c[this._s1 = this._si = 0]
                } else if (t < this._l1 && 0 < n) {
                    for (; 0 < n && (this._l1 = u[--n]) >= t; )
                        ;
                    0 === n && t < this._l1 ? this._l1 = 0 : n++,
                    this._l2 = u[n],
                    this._li = n,
                    this._curSeg = c = this._segments[n],
                    this._s1 = c[(this._si = c.length - 1) - 1] || 0,
                    this._s2 = c[this._si]
                }
                if (e = n,
                t -= this._l1,
                n = this._si,
                t > this._s2 && n < c.length - 1) {
                    for (l = c.length - 1; n < l && (this._s2 = c[++n]) <= t; )
                        ;
                    this._s1 = c[n - 1],
                    this._si = n
                } else if (t < this._s1 && 0 < n) {
                    for (; 0 < n && (this._s1 = c[--n]) >= t; )
                        ;
                    0 === n && t < this._s1 ? this._s1 = 0 : n++,
                    this._s2 = c[n],
                    this._si = n
                }
                s = (n + (t - this._s1) / (this._s2 - this._s1)) * this._prec || 0
            } else
                s = (t - (e = t < 0 ? 0 : 1 <= t ? h - 1 : h * t >> 0) * (1 / h)) * h;
            for (i = 1 - s,
            n = this._props.length; -1 < --n; )
                r = this._props[n],
                a = (s * s * (o = this._beziers[r][e]).da + 3 * i * (s * o.ca + i * o.ba)) * s + o.a,
                this._mod[r] && (a = this._mod[r](a, d)),
                f[r] ? d[r](a) : d[r] = a;
            if (this._autoRotate) {
                var g, _, m, v, y, w, b, x = this._autoRotate;
                for (n = x.length; -1 < --n; )
                    r = x[n][2],
                    w = x[n][3] || 0,
                    b = !0 === x[n][4] ? 1 : T,
                    o = this._beziers[x[n][0]],
                    g = this._beziers[x[n][1]],
                    o && g && (o = o[e],
                    g = g[e],
                    _ = o.a + (o.b - o.a) * s,
                    _ += ((v = o.b + (o.c - o.b) * s) - _) * s,
                    v += (o.c + (o.d - o.c) * s - v) * s,
                    m = g.a + (g.b - g.a) * s,
                    m += ((y = g.b + (g.c - g.b) * s) - m) * s,
                    y += (g.c + (g.d - g.c) * s - y) * s,
                    a = p ? Math.atan2(y - m, v - _) * b + w : this._initialRotations[n],
                    this._mod[r] && (a = this._mod[r](a, d)),
                    f[r] ? d[r](a) : d[r] = a)
            }
        }
    }),
    n = _.prototype,
    _.bezierThrough = d,
    _.cubicToQuadratic = k,
    _._autoCSS = !0,
    _.quadraticToCubic = function(t, e, i) {
        return new v(t,(2 * e + t) / 3,(2 * e + i) / 3,i)
    }
    ,
    _._cssRegister = function() {
        var t = i.CSSPlugin;
        if (t) {
            var e = t._internals
              , d = e._parseToProxy
              , p = e._setPluginRatio
              , g = e.CSSPropTween;
            e._registerComplexSpecialProp("bezier", {
                parser: function(t, e, i, n, r, o) {
                    e instanceof Array && (e = {
                        values: e
                    }),
                    o = new _;
                    var s, a, l, u = e.values, c = u.length - 1, h = [], f = {};
                    if (c < 0)
                        return r;
                    for (s = 0; s <= c; s++)
                        l = d(t, u[s], n, r, o, c !== s),
                        h[s] = l.end;
                    for (a in e)
                        f[a] = e[a];
                    return f.values = h,
                    (r = new g(t,"bezier",0,0,l.pt,2)).data = l,
                    r.plugin = o,
                    r.setRatio = p,
                    0 === f.autoRotate && (f.autoRotate = !0),
                    !f.autoRotate || f.autoRotate instanceof Array || (s = !0 === f.autoRotate ? 0 : Number(f.autoRotate),
                    f.autoRotate = null != l.end.left ? [["left", "top", "rotation", s, !1]] : null != l.end.x && [["x", "y", "rotation", s, !1]]),
                    f.autoRotate && (n._transform || n._enableTransforms(!1),
                    l.autoRotate = n._target._gsTransform,
                    l.proxy.rotation = l.autoRotate.rotation || 0,
                    n._overwriteProps.push("rotation")),
                    o._onInitTween(l.proxy, f, n._tween),
                    r
                }
            })
        }
    }
    ,
    n._mod = function(t) {
        for (var e, i = this._overwriteProps, n = i.length; -1 < --n; )
            (e = t[i[n]]) && "function" == typeof e && (this._mod[i[n]] = e)
    }
    ,
    n._kill = function(t) {
        var e, i, n = this._props;
        for (e in this._beziers)
            if (e in t)
                for (delete this._beziers[e],
                delete this._func[e],
                i = n.length; -1 < --i; )
                    n[i] === e && n.splice(i, 1);
        if (n = this._autoRotate)
            for (i = n.length; -1 < --i; )
                t[n[i][2]] && n.splice(i, 1);
        return this._super._kill.call(this, t)
    }
    ,
    _gsScope._gsDefine("plugins.CSSPlugin", ["plugins.TweenPlugin", "TweenLite"], function(o, B) {
        var p, C, S, g, L = function() {
            o.call(this, "css"),
            this._overwriteProps.length = 0,
            this.setRatio = L.prototype.setRatio
        }, u = _gsScope._gsDefine.globals, _ = {}, t = L.prototype = new o("css");
        (t.constructor = L).version = "2.0.2",
        L.API = 2,
        L.defaultTransformPerspective = 0,
        L.defaultSkewType = "compensated",
        L.defaultSmoothOrigin = !0,
        t = "px",
        L.suffixMap = {
            top: t,
            right: t,
            bottom: t,
            left: t,
            width: t,
            height: t,
            fontSize: t,
            padding: t,
            margin: t,
            perspective: t,
            lineHeight: ""
        };
        var $, m, v, F, y, k, D, A, e, i, E = /(?:\-|\.|\b)(\d|\.|e\-)+/g, P = /(?:\d|\-\d|\.\d|\-\.\d|\+=\d|\-=\d|\+=.\d|\-=\.\d)+/g, w = /(?:\+=|\-=|\-|\b)[\d\-\.]+[a-zA-Z0-9]*(?:%|\b)/gi, c = /(?![+-]?\d*\.?\d+|[+-]|e[+-]\d+)[^0-9]/g, O = /(?:\d|\-|\+|=|#|\.)*/g, R = /opacity *= *([^)]*)/i, b = /opacity:([^;]*)/i, s = /alpha\(opacity *=.+?\)/i, x = /^(rgb|hsl)/, a = /([A-Z])/g, l = /-([a-z])/gi, T = /(^(?:url\(\"|url\())|(?:(\"\))$|\)$)/gi, h = function(t, e) {
            return e.toUpperCase()
        }, d = /(?:Left|Right|Width)/i, f = /(M11|M12|M21|M22)=[\d\-\.e]+/gi, M = /progid\:DXImageTransform\.Microsoft\.Matrix\(.+?\)/i, j = /,(?=[^\)]*(?:\(|$))/gi, z = /[\s,\(]/i, I = Math.PI / 180, q = 180 / Math.PI, N = {}, n = {
            style: {}
        }, W = _gsScope.document || {
            createElement: function() {
                return n
            }
        }, H = function(t, e) {
            return W.createElementNS ? W.createElementNS(e || "http://www.w3.org/1999/xhtml", t) : W.createElement(t)
        }, U = H("div"), V = H("img"), r = L._internals = {
            _specialProps: _
        }, X = (_gsScope.navigator || {}).userAgent || "", G = (e = X.indexOf("Android"),
        i = H("a"),
        v = -1 !== X.indexOf("Safari") && -1 === X.indexOf("Chrome") && (-1 === e || 3 < parseFloat(X.substr(e + 8, 2))),
        y = v && parseFloat(X.substr(X.indexOf("Version/") + 8, 2)) < 6,
        F = -1 !== X.indexOf("Firefox"),
        (/MSIE ([0-9]{1,}[\.0-9]{0,})/.exec(X) || /Trident\/.*rv:([0-9]{1,}[\.0-9]{0,})/.exec(X)) && (k = parseFloat(RegExp.$1)),
        !!i && (i.style.cssText = "top:1px;opacity:.55;",
        /^0.55/.test(i.style.opacity))), Y = function(t) {
            return R.test("string" == typeof t ? t : (t.currentStyle ? t.currentStyle.filter : t.style.filter) || "") ? parseFloat(RegExp.$1) / 100 : 1
        }, Z = function(t) {
            _gsScope.console && console.log(t)
        }, Q = "", K = "", J = function(t, e) {
            var i, n, r = (e = e || U).style;
            if (void 0 !== r[t])
                return t;
            for (t = t.charAt(0).toUpperCase() + t.substr(1),
            i = ["O", "Moz", "ms", "Ms", "Webkit"],
            n = 5; -1 < --n && void 0 === r[i[n] + t]; )
                ;
            return 0 <= n ? (Q = "-" + (K = 3 === n ? "ms" : i[n]).toLowerCase() + "-",
            K + t) : null
        }, tt = ("undefined" != typeof window ? window : W.defaultView || {
            getComputedStyle: function() {}
        }).getComputedStyle, et = L.getStyle = function(t, e, i, n, r) {
            var o;
            return G || "opacity" !== e ? (!n && t.style[e] ? o = t.style[e] : (i = i || tt(t)) ? o = i[e] || i.getPropertyValue(e) || i.getPropertyValue(e.replace(a, "-$1").toLowerCase()) : t.currentStyle && (o = t.currentStyle[e]),
            null == r || o && "none" !== o && "auto" !== o && "auto auto" !== o ? o : r) : Y(t)
        }
        , it = r.convertToPixels = function(t, e, i, n, r) {
            if ("px" === n || !n && "lineHeight" !== e)
                return i;
            if ("auto" === n || !i)
                return 0;
            var o, s, a, l = d.test(e), u = t, c = U.style, h = i < 0, f = 1 === i;
            if (h && (i = -i),
            f && (i *= 100),
            "lineHeight" !== e || n)
                if ("%" === n && -1 !== e.indexOf("border"))
                    o = i / 100 * (l ? t.clientWidth : t.clientHeight);
                else {
                    if (c.cssText = "border:0 solid red;position:" + et(t, "position") + ";line-height:0;",
                    "%" !== n && u.appendChild && "v" !== n.charAt(0) && "rem" !== n)
                        c[l ? "borderLeftWidth" : "borderTopWidth"] = i + n;
                    else {
                        if (u = t.parentNode || W.body,
                        -1 !== et(u, "display").indexOf("flex") && (c.position = "absolute"),
                        s = u._gsCache,
                        a = B.ticker.frame,
                        s && l && s.time === a)
                            return s.width * i / 100;
                        c[l ? "width" : "height"] = i + n
                    }
                    u.appendChild(U),
                    o = parseFloat(U[l ? "offsetWidth" : "offsetHeight"]),
                    u.removeChild(U),
                    l && "%" === n && !1 !== L.cacheWidths && ((s = u._gsCache = u._gsCache || {}).time = a,
                    s.width = o / i * 100),
                    0 !== o || r || (o = it(t, e, i, n, !0))
                }
            else
                s = tt(t).lineHeight,
                t.style.lineHeight = i,
                o = parseFloat(tt(t).lineHeight),
                t.style.lineHeight = s;
            return f && (o /= 100),
            h ? -o : o
        }
        , nt = r.calculateOffset = function(t, e, i) {
            if ("absolute" !== et(t, "position", i))
                return 0;
            var n = "left" === e ? "Left" : "Top"
              , r = et(t, "margin" + n, i);
            return t["offset" + n] - (it(t, e, parseFloat(r), r.replace(O, "")) || 0)
        }
        , rt = function(t, e) {
            var i, n, r, o = {};
            if (e = e || tt(t, null))
                if (i = e.length)
                    for (; -1 < --i; )
                        (-1 === (r = e[i]).indexOf("-transform") || jt === r) && (o[r.replace(l, h)] = e.getPropertyValue(r));
                else
                    for (i in e)
                        (-1 === i.indexOf("Transform") || Mt === i) && (o[i] = e[i]);
            else if (e = t.currentStyle || t.style)
                for (i in e)
                    "string" == typeof i && void 0 === o[i] && (o[i.replace(l, h)] = e[i]);
            return G || (o.opacity = Y(t)),
            n = Gt(t, e, !1),
            o.rotation = n.rotation,
            o.skewX = n.skewX,
            o.scaleX = n.scaleX,
            o.scaleY = n.scaleY,
            o.x = n.x,
            o.y = n.y,
            Ft && (o.z = n.z,
            o.rotationX = n.rotationX,
            o.rotationY = n.rotationY,
            o.scaleZ = n.scaleZ),
            o.filters && delete o.filters,
            o
        }, ot = function(t, e, i, n, r) {
            var o, s, a, l = {}, u = t.style;
            for (s in i)
                "cssText" !== s && "length" !== s && isNaN(s) && (e[s] !== (o = i[s]) || r && r[s]) && -1 === s.indexOf("Origin") && ("number" == typeof o || "string" == typeof o) && (l[s] = "auto" !== o || "left" !== s && "top" !== s ? "" !== o && "auto" !== o && "none" !== o || "string" != typeof e[s] || "" === e[s].replace(c, "") ? o : 0 : nt(t, s),
                void 0 !== u[s] && (a = new wt(u,s,u[s],a)));
            if (n)
                for (s in n)
                    "className" !== s && (l[s] = n[s]);
            return {
                difs: l,
                firstMPT: a
            }
        }, st = {
            width: ["Left", "Right"],
            height: ["Top", "Bottom"]
        }, at = ["marginLeft", "marginRight", "marginTop", "marginBottom"], lt = function(t, e, i) {
            if ("svg" === (t.nodeName + "").toLowerCase())
                return (i || tt(t))[e] || 0;
            if (t.getCTM && Ut(t))
                return t.getBBox()[e] || 0;
            var n = parseFloat("width" === e ? t.offsetWidth : t.offsetHeight)
              , r = st[e]
              , o = r.length;
            for (i = i || tt(t, null); -1 < --o; )
                n -= parseFloat(et(t, "padding" + r[o], i, !0)) || 0,
                n -= parseFloat(et(t, "border" + r[o] + "Width", i, !0)) || 0;
            return n
        }, ut = function(t, e) {
            if ("contain" === t || "auto" === t || "auto auto" === t)
                return t + " ";
            (null == t || "" === t) && (t = "0 0");
            var i, n = t.split(" "), r = -1 !== t.indexOf("left") ? "0%" : -1 !== t.indexOf("right") ? "100%" : n[0], o = -1 !== t.indexOf("top") ? "0%" : -1 !== t.indexOf("bottom") ? "100%" : n[1];
            if (3 < n.length && !e) {
                for (n = t.split(", ").join(",").split(","),
                t = [],
                i = 0; i < n.length; i++)
                    t.push(ut(n[i]));
                return t.join(",")
            }
            return null == o ? o = "center" === r ? "50%" : "0" : "center" === o && (o = "50%"),
            ("center" === r || isNaN(parseFloat(r)) && -1 === (r + "").indexOf("=")) && (r = "50%"),
            t = r + " " + o + (2 < n.length ? " " + n[2] : ""),
            e && (e.oxp = -1 !== r.indexOf("%"),
            e.oyp = -1 !== o.indexOf("%"),
            e.oxr = "=" === r.charAt(1),
            e.oyr = "=" === o.charAt(1),
            e.ox = parseFloat(r.replace(c, "")),
            e.oy = parseFloat(o.replace(c, "")),
            e.v = t),
            e || t
        }, ct = function(t, e) {
            return "function" == typeof t && (t = t(A, D)),
            "string" == typeof t && "=" === t.charAt(1) ? parseInt(t.charAt(0) + "1", 10) * parseFloat(t.substr(2)) : parseFloat(t) - parseFloat(e) || 0
        }, ht = function(t, e) {
            "function" == typeof t && (t = t(A, D));
            var i = "string" == typeof t && "=" === t.charAt(1);
            return "string" == typeof t && "v" === t.charAt(t.length - 2) && (t = (i ? t.substr(0, 2) : 0) + window["inner" + ("vh" === t.substr(-2) ? "Height" : "Width")] * (parseFloat(i ? t.substr(2) : t) / 100)),
            null == t ? e : i ? parseInt(t.charAt(0) + "1", 10) * parseFloat(t.substr(2)) + e : parseFloat(t) || 0
        }, ft = function(t, e, i, n) {
            var r, o, s, a;
            return "function" == typeof t && (t = t(A, D)),
            (s = null == t ? e : "number" == typeof t ? t : (360,
            r = t.split("_"),
            o = ((a = "=" === t.charAt(1)) ? parseInt(t.charAt(0) + "1", 10) * parseFloat(r[0].substr(2)) : parseFloat(r[0])) * (-1 === t.indexOf("rad") ? 1 : q) - (a ? 0 : e),
            r.length && (n && (n[i] = e + o),
            -1 !== t.indexOf("short") && ((o %= 360) !== o % 180 && (o = o < 0 ? o + 360 : o - 360)),
            -1 !== t.indexOf("_cw") && o < 0 ? o = (o + 3599999999640) % 360 - 360 * (o / 360 | 0) : -1 !== t.indexOf("ccw") && 0 < o && (o = (o - 3599999999640) % 360 - 360 * (o / 360 | 0))),
            e + o)) < 1e-6 && -1e-6 < s && (s = 0),
            s
        }, dt = {
            aqua: [0, 255, 255],
            lime: [0, 255, 0],
            silver: [192, 192, 192],
            black: [0, 0, 0],
            maroon: [128, 0, 0],
            teal: [0, 128, 128],
            blue: [0, 0, 255],
            navy: [0, 0, 128],
            white: [255, 255, 255],
            fuchsia: [255, 0, 255],
            olive: [128, 128, 0],
            yellow: [255, 255, 0],
            orange: [255, 165, 0],
            gray: [128, 128, 128],
            purple: [128, 0, 128],
            green: [0, 128, 0],
            red: [255, 0, 0],
            pink: [255, 192, 203],
            cyan: [0, 255, 255],
            transparent: [255, 255, 255, 0]
        }, pt = function(t, e, i) {
            return 255 * (6 * (t = t < 0 ? t + 1 : 1 < t ? t - 1 : t) < 1 ? e + (i - e) * t * 6 : t < .5 ? i : 3 * t < 2 ? e + (i - e) * (2 / 3 - t) * 6 : e) + .5 | 0
        }, gt = L.parseColor = function(t, e) {
            var i, n, r, o, s, a, l, u, c, h, f;
            if (t)
                if ("number" == typeof t)
                    i = [t >> 16, t >> 8 & 255, 255 & t];
                else {
                    if ("," === t.charAt(t.length - 1) && (t = t.substr(0, t.length - 1)),
                    dt[t])
                        i = dt[t];
                    else if ("#" === t.charAt(0))
                        4 === t.length && (t = "#" + (n = t.charAt(1)) + n + (r = t.charAt(2)) + r + (o = t.charAt(3)) + o),
                        i = [(t = parseInt(t.substr(1), 16)) >> 16, t >> 8 & 255, 255 & t];
                    else if ("hsl" === t.substr(0, 3))
                        if (i = f = t.match(E),
                        e) {
                            if (-1 !== t.indexOf("="))
                                return t.match(P)
                        } else
                            s = Number(i[0]) % 360 / 360,
                            a = Number(i[1]) / 100,
                            n = 2 * (l = Number(i[2]) / 100) - (r = l <= .5 ? l * (a + 1) : l + a - l * a),
                            3 < i.length && (i[3] = Number(i[3])),
                            i[0] = pt(s + 1 / 3, n, r),
                            i[1] = pt(s, n, r),
                            i[2] = pt(s - 1 / 3, n, r);
                    else
                        i = t.match(E) || dt.transparent;
                    i[0] = Number(i[0]),
                    i[1] = Number(i[1]),
                    i[2] = Number(i[2]),
                    3 < i.length && (i[3] = Number(i[3]))
                }
            else
                i = dt.black;
            return e && !f && (n = i[0] / 255,
            r = i[1] / 255,
            o = i[2] / 255,
            l = ((u = Math.max(n, r, o)) + (c = Math.min(n, r, o))) / 2,
            u === c ? s = a = 0 : (h = u - c,
            a = .5 < l ? h / (2 - u - c) : h / (u + c),
            s = u === n ? (r - o) / h + (r < o ? 6 : 0) : u === r ? (o - n) / h + 2 : (n - r) / h + 4,
            s *= 60),
            i[0] = s + .5 | 0,
            i[1] = 100 * a + .5 | 0,
            i[2] = 100 * l + .5 | 0),
            i
        }
        , _t = function(t, e) {
            var i, n, r, o = t.match(mt) || [], s = 0, a = "";
            if (!o.length)
                return t;
            for (i = 0; i < o.length; i++)
                n = o[i],
                s += (r = t.substr(s, t.indexOf(n, s) - s)).length + n.length,
                3 === (n = gt(n, e)).length && n.push(1),
                a += r + (e ? "hsla(" + n[0] + "," + n[1] + "%," + n[2] + "%," + n[3] : "rgba(" + n.join(",")) + ")";
            return a + t.substr(s)
        }, mt = "(?:\\b(?:(?:rgb|rgba|hsl|hsla)\\(.+?\\))|\\B#(?:[0-9a-f]{3}){1,2}\\b";
        for (t in dt)
            mt += "|" + t + "\\b";
        mt = new RegExp(mt + ")","gi"),
        L.colorStringFilter = function(t) {
            var e, i = t[0] + " " + t[1];
            mt.test(i) && (e = -1 !== i.indexOf("hsl(") || -1 !== i.indexOf("hsla("),
            t[0] = _t(t[0], e),
            t[1] = _t(t[1], e)),
            mt.lastIndex = 0
        }
        ,
        B.defaultStringFilter || (B.defaultStringFilter = L.colorStringFilter);
        var vt = function(t, e, o, s) {
            if (null == t)
                return function(t) {
                    return t
                }
                ;
            var a, l = e ? (t.match(mt) || [""])[0] : "", u = t.split(l).join("").match(w) || [], c = t.substr(0, t.indexOf(u[0])), h = ")" === t.charAt(t.length - 1) ? ")" : "", f = -1 !== t.indexOf(" ") ? " " : ",", d = u.length, p = 0 < d ? u[0].replace(E, "") : "";
            return d ? a = e ? function(t) {
                var e, i, n, r;
                if ("number" == typeof t)
                    t += p;
                else if (s && j.test(t)) {
                    for (r = t.replace(j, "|").split("|"),
                    n = 0; n < r.length; n++)
                        r[n] = a(r[n]);
                    return r.join(",")
                }
                if (e = (t.match(mt) || [l])[0],
                n = (i = t.split(e).join("").match(w) || []).length,
                d > n--)
                    for (; ++n < d; )
                        i[n] = o ? i[(n - 1) / 2 | 0] : u[n];
                return c + i.join(f) + f + e + h + (-1 !== t.indexOf("inset") ? " inset" : "")
            }
            : function(t) {
                var e, i, n;
                if ("number" == typeof t)
                    t += p;
                else if (s && j.test(t)) {
                    for (i = t.replace(j, "|").split("|"),
                    n = 0; n < i.length; n++)
                        i[n] = a(i[n]);
                    return i.join(",")
                }
                if (n = (e = t.match(w) || []).length,
                d > n--)
                    for (; ++n < d; )
                        e[n] = o ? e[(n - 1) / 2 | 0] : u[n];
                return c + e.join(f) + h
            }
            : function(t) {
                return t
            }
        }
          , yt = function(u) {
            return u = u.split(","),
            function(t, e, i, n, r, o, s) {
                var a, l = (e + "").split(" ");
                for (s = {},
                a = 0; a < 4; a++)
                    s[u[a]] = l[a] = l[a] || l[(a - 1) / 2 >> 0];
                return n.parse(t, s, r, o)
            }
        }
          , wt = (r._setPluginRatio = function(t) {
            this.plugin.setRatio(t);
            for (var e, i, n, r, o, s = this.data, a = s.proxy, l = s.firstMPT; l; )
                e = a[l.v],
                l.r ? e = l.r(e) : e < 1e-6 && -1e-6 < e && (e = 0),
                l.t[l.p] = e,
                l = l._next;
            if (s.autoRotate && (s.autoRotate.rotation = s.mod ? s.mod.call(this._tween, a.rotation, this.t, this._tween) : a.rotation),
            1 === t || 0 === t)
                for (l = s.firstMPT,
                o = 1 === t ? "e" : "b"; l; ) {
                    if ((i = l.t).type) {
                        if (1 === i.type) {
                            for (r = i.xs0 + i.s + i.xs1,
                            n = 1; n < i.l; n++)
                                r += i["xn" + n] + i["xs" + (n + 1)];
                            i[o] = r
                        }
                    } else
                        i[o] = i.s + i.xs0;
                    l = l._next
                }
        }
        ,
        function(t, e, i, n, r) {
            this.t = t,
            this.p = e,
            this.v = i,
            this.r = r,
            n && ((n._prev = this)._next = n)
        }
        )
          , bt = (r._parseToProxy = function(t, e, i, n, r, o) {
            var s, a, l, u, c, h = n, f = {}, d = {}, p = i._transform, g = N;
            for (i._transform = null,
            N = e,
            n = c = i.parse(t, e, n, r),
            N = g,
            o && (i._transform = p,
            h && (h._prev = null,
            h._prev && (h._prev._next = null))); n && n !== h; ) {
                if (n.type <= 1 && (d[a = n.p] = n.s + n.c,
                f[a] = n.s,
                o || (u = new wt(n,"s",a,u,n.r),
                n.c = 0),
                1 === n.type))
                    for (s = n.l; 0 < --s; )
                        l = "xn" + s,
                        d[a = n.p + "_" + l] = n.data[l],
                        f[a] = n[l],
                        o || (u = new wt(n,l,a,u,n.rxp[l]));
                n = n._next
            }
            return {
                proxy: f,
                end: d,
                firstMPT: u,
                pt: c
            }
        }
        ,
        r.CSSPropTween = function(t, e, i, n, r, o, s, a, l, u, c) {
            this.t = t,
            this.p = e,
            this.s = i,
            this.c = n,
            this.n = s || e,
            t instanceof bt || g.push(this.n),
            this.r = a ? "function" == typeof a ? a : Math.round : a,
            this.type = o || 0,
            l && (this.pr = l,
            p = !0),
            this.b = void 0 === u ? i : u,
            this.e = void 0 === c ? i + n : c,
            r && ((this._next = r)._prev = this)
        }
        )
          , xt = function(t, e, i, n, r, o) {
            var s = new bt(t,e,i,n - i,r,-1,o);
            return s.b = i,
            s.e = s.xs0 = n,
            s
        }
          , Tt = L.parseComplex = function(t, e, i, n, r, o, s, a, l, u) {
            i = i || o || "",
            "function" == typeof n && (n = n(A, D)),
            s = new bt(t,e,0,0,s,u ? 2 : 1,null,!1,a,i,n),
            n += "",
            r && mt.test(n + i) && (n = [i, n],
            L.colorStringFilter(n),
            i = n[0],
            n = n[1]);
            var c, h, f, d, p, g, _, m, v, y, w, b, x, T = i.split(", ").join(",").split(" "), C = n.split(", ").join(",").split(" "), k = T.length, S = !1 !== $;
            for ((-1 !== n.indexOf(",") || -1 !== i.indexOf(",")) && (C = -1 !== (n + i).indexOf("rgb") || -1 !== (n + i).indexOf("hsl") ? (T = T.join(" ").replace(j, ", ").split(" "),
            C.join(" ").replace(j, ", ").split(" ")) : (T = T.join(" ").split(",").join(", ").split(" "),
            C.join(" ").split(",").join(", ").split(" ")),
            k = T.length),
            k !== C.length && (k = (T = (o || "").split(" ")).length),
            s.plugin = l,
            s.setRatio = u,
            c = mt.lastIndex = 0; c < k; c++)
                if (d = T[c],
                p = C[c] + "",
                (m = parseFloat(d)) || 0 === m)
                    s.appendXtra("", m, ct(p, m), p.replace(P, ""), !(!S || -1 === p.indexOf("px")) && Math.round, !0);
                else if (r && mt.test(d))
                    b = ")" + ((b = p.indexOf(")") + 1) ? p.substr(b) : ""),
                    x = -1 !== p.indexOf("hsl") && G,
                    y = p,
                    d = gt(d, x),
                    p = gt(p, x),
                    (v = 6 < d.length + p.length) && !G && 0 === p[3] ? (s["xs" + s.l] += s.l ? " transparent" : "transparent",
                    s.e = s.e.split(C[c]).join("transparent")) : (G || (v = !1),
                    x ? s.appendXtra(y.substr(0, y.indexOf("hsl")) + (v ? "hsla(" : "hsl("), d[0], ct(p[0], d[0]), ",", !1, !0).appendXtra("", d[1], ct(p[1], d[1]), "%,", !1).appendXtra("", d[2], ct(p[2], d[2]), v ? "%," : "%" + b, !1) : s.appendXtra(y.substr(0, y.indexOf("rgb")) + (v ? "rgba(" : "rgb("), d[0], p[0] - d[0], ",", Math.round, !0).appendXtra("", d[1], p[1] - d[1], ",", Math.round).appendXtra("", d[2], p[2] - d[2], v ? "," : b, Math.round),
                    v && (d = d.length < 4 ? 1 : d[3],
                    s.appendXtra("", d, (p.length < 4 ? 1 : p[3]) - d, b, !1))),
                    mt.lastIndex = 0;
                else if (g = d.match(E)) {
                    if (!(_ = p.match(P)) || _.length !== g.length)
                        return s;
                    for (h = f = 0; h < g.length; h++)
                        w = g[h],
                        y = d.indexOf(w, f),
                        s.appendXtra(d.substr(f, y - f), Number(w), ct(_[h], w), "", !(!S || "px" !== d.substr(y + w.length, 2)) && Math.round, 0 === h),
                        f = y + w.length;
                    s["xs" + s.l] += d.substr(f)
                } else
                    s["xs" + s.l] += s.l || s["xs" + s.l] ? " " + p : p;
            if (-1 !== n.indexOf("=") && s.data) {
                for (b = s.xs0 + s.data.s,
                c = 1; c < s.l; c++)
                    b += s["xs" + c] + s.data["xn" + c];
                s.e = b + s["xs" + c]
            }
            return s.l || (s.type = -1,
            s.xs0 = s.e),
            s.xfirst || s
        }
          , Ct = 9;
        for ((t = bt.prototype).l = t.pr = 0; 0 < --Ct; )
            t["xn" + Ct] = 0,
            t["xs" + Ct] = "";
        t.xs0 = "",
        t._next = t._prev = t.xfirst = t.data = t.plugin = t.setRatio = t.rxp = null,
        t.appendXtra = function(t, e, i, n, r, o) {
            var s = this
              , a = s.l;
            return s["xs" + a] += o && (a || s["xs" + a]) ? " " + t : t || "",
            i || 0 === a || s.plugin ? (s.l++,
            s.type = s.setRatio ? 2 : 1,
            s["xs" + s.l] = n || "",
            0 < a ? (s.data["xn" + a] = e + i,
            s.rxp["xn" + a] = r,
            s["xn" + a] = e,
            s.plugin || (s.xfirst = new bt(s,"xn" + a,e,i,s.xfirst || s,0,s.n,r,s.pr),
            s.xfirst.xs0 = 0)) : (s.data = {
                s: e + i
            },
            s.rxp = {},
            s.s = e,
            s.c = i,
            s.r = r)) : s["xs" + a] += e + (n || ""),
            s
        }
        ;
        var kt = function(t, e) {
            e = e || {},
            this.p = e.prefix && J(t) || t,
            _[t] = _[this.p] = this,
            this.format = e.formatter || vt(e.defaultValue, e.color, e.collapsible, e.multi),
            e.parser && (this.parse = e.parser),
            this.clrs = e.color,
            this.multi = e.multi,
            this.keyword = e.keyword,
            this.dflt = e.defaultValue,
            this.pr = e.priority || 0
        }
          , St = r._registerComplexSpecialProp = function(t, e, i) {
            "object" != typeof e && (e = {
                parser: i
            });
            var n, r = t.split(","), o = e.defaultValue;
            for (i = i || [o],
            n = 0; n < r.length; n++)
                e.prefix = 0 === n && e.prefix,
                e.defaultValue = i[n] || o,
                new kt(r[n],e)
        }
          , $t = r._registerPluginProp = function(t) {
            if (!_[t]) {
                var l = t.charAt(0).toUpperCase() + t.substr(1) + "Plugin";
                St(t, {
                    parser: function(t, e, i, n, r, o, s) {
                        var a = u.com.greensock.plugins[l];
                        return a ? (a._cssRegister(),
                        _[i].parse(t, e, i, n, r, o, s)) : (Z("Error: " + l + " js file not loaded."),
                        r)
                    }
                })
            }
        }
        ;
        (t = kt.prototype).parseComplex = function(t, e, i, n, r, o) {
            var s, a, l, u, c, h, f = this.keyword;
            if (this.multi && (j.test(i) || j.test(e) ? (a = e.replace(j, "|").split("|"),
            l = i.replace(j, "|").split("|")) : f && (a = [e],
            l = [i])),
            l) {
                for (u = l.length > a.length ? l.length : a.length,
                s = 0; s < u; s++)
                    e = a[s] = a[s] || this.dflt,
                    i = l[s] = l[s] || this.dflt,
                    f && ((c = e.indexOf(f)) !== (h = i.indexOf(f)) && (-1 === h ? a[s] = a[s].split(f).join("") : -1 === c && (a[s] += " " + f)));
                e = a.join(", "),
                i = l.join(", ")
            }
            return Tt(t, this.p, e, i, this.clrs, this.dflt, n, this.pr, r, o)
        }
        ,
        t.parse = function(t, e, i, n, r, o, s) {
            return this.parseComplex(t.style, this.format(et(t, this.p, S, !1, this.dflt)), this.format(e), r, o)
        }
        ,
        L.registerSpecialProp = function(t, l, u) {
            St(t, {
                parser: function(t, e, i, n, r, o, s) {
                    var a = new bt(t,i,0,0,r,2,i,!1,u);
                    return a.plugin = o,
                    a.setRatio = l(t, e, n._tween, i),
                    a
                },
                priority: u
            })
        }
        ,
        L.useSVGTransformAttr = !0;
        var Dt, At, Et, Pt, Ot, Rt = "scaleX,scaleY,scaleZ,x,y,z,skewX,skewY,rotation,rotationX,rotationY,perspective,xPercent,yPercent".split(","), Mt = J("transform"), jt = Q + "transform", zt = J("transformOrigin"), Ft = null !== J("perspective"), It = r.Transform = function() {
            this.perspective = parseFloat(L.defaultTransformPerspective) || 0,
            this.force3D = !(!1 === L.defaultForce3D || !Ft) && (L.defaultForce3D || "auto")
        }
        , Nt = _gsScope.SVGElement, Bt = function(t, e, i) {
            var n, r = W.createElementNS("http://www.w3.org/2000/svg", t), o = /([a-z])([A-Z])/g;
            for (n in i)
                r.setAttributeNS(null, n.replace(o, "$1-$2").toLowerCase(), i[n]);
            return e.appendChild(r),
            r
        }, Lt = W.documentElement || {}, qt = (Ot = k || /Android/i.test(X) && !_gsScope.chrome,
        W.createElementNS && !Ot && (At = Bt("svg", Lt),
        Pt = (Et = Bt("rect", At, {
            width: 100,
            height: 50,
            x: 100
        })).getBoundingClientRect().width,
        Et.style[zt] = "50% 50%",
        Et.style[Mt] = "scaleX(0.5)",
        Ot = Pt === Et.getBoundingClientRect().width && !(F && Ft),
        Lt.removeChild(At)),
        Ot), Wt = function(t, e, i, n, r, o) {
            var s, a, l, u, c, h, f, d, p, g, _, m, v, y, w = t._gsTransform, b = Xt(t, !0);
            w && (v = w.xOrigin,
            y = w.yOrigin),
            (!n || (s = n.split(" ")).length < 2) && (0 === (f = t.getBBox()).x && 0 === f.y && f.width + f.height === 0 && (f = {
                x: parseFloat(t.hasAttribute("x") ? t.getAttribute("x") : t.hasAttribute("cx") ? t.getAttribute("cx") : 0) || 0,
                y: parseFloat(t.hasAttribute("y") ? t.getAttribute("y") : t.hasAttribute("cy") ? t.getAttribute("cy") : 0) || 0,
                width: 0,
                height: 0
            }),
            s = [(-1 !== (e = ut(e).split(" "))[0].indexOf("%") ? parseFloat(e[0]) / 100 * f.width : parseFloat(e[0])) + f.x, (-1 !== e[1].indexOf("%") ? parseFloat(e[1]) / 100 * f.height : parseFloat(e[1])) + f.y]),
            i.xOrigin = u = parseFloat(s[0]),
            i.yOrigin = c = parseFloat(s[1]),
            n && b !== Vt && (h = b[0],
            f = b[1],
            d = b[2],
            p = b[3],
            g = b[4],
            _ = b[5],
            (m = h * p - f * d) && (a = u * (p / m) + c * (-d / m) + (d * _ - p * g) / m,
            l = u * (-f / m) + c * (h / m) - (h * _ - f * g) / m,
            u = i.xOrigin = s[0] = a,
            c = i.yOrigin = s[1] = l)),
            w && (o && (i.xOffset = w.xOffset,
            i.yOffset = w.yOffset,
            w = i),
            r || !1 !== r && !1 !== L.defaultSmoothOrigin ? (a = u - v,
            l = c - y,
            w.xOffset += a * b[0] + l * b[2] - a,
            w.yOffset += a * b[1] + l * b[3] - l) : w.xOffset = w.yOffset = 0),
            o || t.setAttribute("data-svg-origin", s.join(" "))
        }, Ht = function(t) {
            var e, i = H("svg", this.ownerSVGElement && this.ownerSVGElement.getAttribute("xmlns") || "http://www.w3.org/2000/svg"), n = this.parentNode, r = this.nextSibling, o = this.style.cssText;
            if (Lt.appendChild(i),
            i.appendChild(this),
            this.style.display = "block",
            t)
                try {
                    e = this.getBBox(),
                    this._originalGetBBox = this.getBBox,
                    this.getBBox = Ht
                } catch (t) {}
            else
                this._originalGetBBox && (e = this._originalGetBBox());
            return r ? n.insertBefore(this, r) : n.appendChild(this),
            Lt.removeChild(i),
            this.style.cssText = o,
            e
        }, Ut = function(t) {
            return !(!Nt || !t.getCTM || t.parentNode && !t.ownerSVGElement || !function(e) {
                try {
                    return e.getBBox()
                } catch (t) {
                    return Ht.call(e, !0)
                }
            }(t))
        }, Vt = [1, 0, 0, 1, 0, 0], Xt = function(t, e) {
            var i, n, r, o, s, a, l = t._gsTransform || new It, u = t.style;
            if (Mt ? n = et(t, jt, null, !0) : t.currentStyle && (n = (n = t.currentStyle.filter.match(f)) && 4 === n.length ? [n[0].substr(4), Number(n[2].substr(4)), Number(n[1].substr(4)), n[3].substr(4), l.x || 0, l.y || 0].join(",") : ""),
            i = !n || "none" === n || "matrix(1, 0, 0, 1, 0, 0)" === n,
            !Mt || !(a = !tt(t) || "none" === tt(t).display) && t.parentNode || (a && (o = u.display,
            u.display = "block"),
            t.parentNode || (s = 1,
            Lt.appendChild(t)),
            i = !(n = et(t, jt, null, !0)) || "none" === n || "matrix(1, 0, 0, 1, 0, 0)" === n,
            o ? u.display = o : a && Kt(u, "display"),
            s && Lt.removeChild(t)),
            (l.svg || t.getCTM && Ut(t)) && (i && -1 !== (u[Mt] + "").indexOf("matrix") && (n = u[Mt],
            i = 0),
            r = t.getAttribute("transform"),
            i && r && (n = "matrix(" + (r = t.transform.baseVal.consolidate().matrix).a + "," + r.b + "," + r.c + "," + r.d + "," + r.e + "," + r.f + ")",
            i = 0)),
            i)
                return Vt;
            for (r = (n || "").match(E) || [],
            Ct = r.length; -1 < --Ct; )
                o = Number(r[Ct]),
                r[Ct] = (s = o - (o |= 0)) ? (1e5 * s + (s < 0 ? -.5 : .5) | 0) / 1e5 + o : o;
            return e && 6 < r.length ? [r[0], r[1], r[4], r[5], r[12], r[13]] : r
        }, Gt = r.getTransform = function(t, e, i, n) {
            if (t._gsTransform && i && !n)
                return t._gsTransform;
            var r, o, s, a, l, u, c = i && t._gsTransform || new It, h = c.scaleX < 0, f = Ft && (parseFloat(et(t, zt, e, !1, "0 0 0").split(" ")[2]) || c.zOrigin) || 0, d = parseFloat(L.defaultTransformPerspective) || 0;
            if (c.svg = !(!t.getCTM || !Ut(t)),
            c.svg && (Wt(t, et(t, zt, e, !1, "50% 50%") + "", c, t.getAttribute("data-svg-origin")),
            Dt = L.useSVGTransformAttr || qt),
            (r = Xt(t)) !== Vt) {
                if (16 === r.length) {
                    var p, g, _, m, v, y = r[0], w = r[1], b = r[2], x = r[3], T = r[4], C = r[5], k = r[6], S = r[7], $ = r[8], D = r[9], A = r[10], E = r[12], P = r[13], O = r[14], R = r[11], M = Math.atan2(k, A);
                    c.zOrigin && (E = $ * (O = -c.zOrigin) - r[12],
                    P = D * O - r[13],
                    O = A * O + c.zOrigin - r[14]),
                    c.rotationX = M * q,
                    M && (p = T * (m = Math.cos(-M)) + $ * (v = Math.sin(-M)),
                    g = C * m + D * v,
                    _ = k * m + A * v,
                    $ = T * -v + $ * m,
                    D = C * -v + D * m,
                    A = k * -v + A * m,
                    R = S * -v + R * m,
                    T = p,
                    C = g,
                    k = _),
                    M = Math.atan2(-b, A),
                    c.rotationY = M * q,
                    M && (g = w * (m = Math.cos(-M)) - D * (v = Math.sin(-M)),
                    _ = b * m - A * v,
                    D = w * v + D * m,
                    A = b * v + A * m,
                    R = x * v + R * m,
                    y = p = y * m - $ * v,
                    w = g,
                    b = _),
                    M = Math.atan2(w, y),
                    c.rotation = M * q,
                    M && (p = y * (m = Math.cos(M)) + w * (v = Math.sin(M)),
                    g = T * m + C * v,
                    _ = $ * m + D * v,
                    w = w * m - y * v,
                    C = C * m - T * v,
                    D = D * m - $ * v,
                    y = p,
                    T = g,
                    $ = _),
                    c.rotationX && 359.9 < Math.abs(c.rotationX) + Math.abs(c.rotation) && (c.rotationX = c.rotation = 0,
                    c.rotationY = 180 - c.rotationY),
                    M = Math.atan2(T, C),
                    c.scaleX = (1e5 * Math.sqrt(y * y + w * w + b * b) + .5 | 0) / 1e5,
                    c.scaleY = (1e5 * Math.sqrt(C * C + k * k) + .5 | 0) / 1e5,
                    c.scaleZ = (1e5 * Math.sqrt($ * $ + D * D + A * A) + .5 | 0) / 1e5,
                    y /= c.scaleX,
                    T /= c.scaleY,
                    w /= c.scaleX,
                    C /= c.scaleY,
                    2e-5 < Math.abs(M) ? (c.skewX = M * q,
                    T = 0,
                    "simple" !== c.skewType && (c.scaleY *= 1 / Math.cos(M))) : c.skewX = 0,
                    c.perspective = R ? 1 / (R < 0 ? -R : R) : 0,
                    c.x = E,
                    c.y = P,
                    c.z = O,
                    c.svg && (c.x -= c.xOrigin - (c.xOrigin * y - c.yOrigin * T),
                    c.y -= c.yOrigin - (c.yOrigin * w - c.xOrigin * C))
                } else if (!Ft || n || !r.length || c.x !== r[4] || c.y !== r[5] || !c.rotationX && !c.rotationY) {
                    var j = 6 <= r.length
                      , z = j ? r[0] : 1
                      , F = r[1] || 0
                      , I = r[2] || 0
                      , N = j ? r[3] : 1;
                    c.x = r[4] || 0,
                    c.y = r[5] || 0,
                    s = Math.sqrt(z * z + F * F),
                    a = Math.sqrt(N * N + I * I),
                    l = z || F ? Math.atan2(F, z) * q : c.rotation || 0,
                    u = I || N ? Math.atan2(I, N) * q + l : c.skewX || 0,
                    c.scaleX = s,
                    c.scaleY = a,
                    c.rotation = l,
                    c.skewX = u,
                    Ft && (c.rotationX = c.rotationY = c.z = 0,
                    c.perspective = d,
                    c.scaleZ = 1),
                    c.svg && (c.x -= c.xOrigin - (c.xOrigin * z + c.yOrigin * I),
                    c.y -= c.yOrigin - (c.xOrigin * F + c.yOrigin * N))
                }
                for (o in 90 < Math.abs(c.skewX) && Math.abs(c.skewX) < 270 && (h ? (c.scaleX *= -1,
                c.skewX += c.rotation <= 0 ? 180 : -180,
                c.rotation += c.rotation <= 0 ? 180 : -180) : (c.scaleY *= -1,
                c.skewX += c.skewX <= 0 ? 180 : -180)),
                c.zOrigin = f,
                c)
                    c[o] < 2e-5 && -2e-5 < c[o] && (c[o] = 0)
            }
            return i && ((t._gsTransform = c).svg && (Dt && t.style[Mt] ? B.delayedCall(.001, function() {
                Kt(t.style, Mt)
            }) : !Dt && t.getAttribute("transform") && B.delayedCall(.001, function() {
                t.removeAttribute("transform")
            }))),
            c
        }
        , Yt = function(t) {
            var e, i, n = this.data, r = -n.rotation * I, o = r + n.skewX * I, s = (Math.cos(r) * n.scaleX * 1e5 | 0) / 1e5, a = (Math.sin(r) * n.scaleX * 1e5 | 0) / 1e5, l = (Math.sin(o) * -n.scaleY * 1e5 | 0) / 1e5, u = (Math.cos(o) * n.scaleY * 1e5 | 0) / 1e5, c = this.t.style, h = this.t.currentStyle;
            if (h) {
                i = a,
                a = -l,
                l = -i,
                e = h.filter,
                c.filter = "";
                var f, d, p = this.t.offsetWidth, g = this.t.offsetHeight, _ = "absolute" !== h.position, m = "progid:DXImageTransform.Microsoft.Matrix(M11=" + s + ", M12=" + a + ", M21=" + l + ", M22=" + u, v = n.x + p * n.xPercent / 100, y = n.y + g * n.yPercent / 100;
                if (null != n.ox && (v += (f = (n.oxp ? p * n.ox * .01 : n.ox) - p / 2) - (f * s + (d = (n.oyp ? g * n.oy * .01 : n.oy) - g / 2) * a),
                y += d - (f * l + d * u)),
                _ ? m += ", Dx=" + ((f = p / 2) - (f * s + (d = g / 2) * a) + v) + ", Dy=" + (d - (f * l + d * u) + y) + ")" : m += ", sizingMethod='auto expand')",
                -1 !== e.indexOf("DXImageTransform.Microsoft.Matrix(") ? c.filter = e.replace(M, m) : c.filter = m + " " + e,
                (0 === t || 1 === t) && 1 === s && 0 === a && 0 === l && 1 === u && (_ && -1 === m.indexOf("Dx=0, Dy=0") || R.test(e) && 100 !== parseFloat(RegExp.$1) || -1 === e.indexOf(e.indexOf("Alpha")) && c.removeAttribute("filter")),
                !_) {
                    var w, b, x, T = k < 8 ? 1 : -1;
                    for (f = n.ieOffsetX || 0,
                    d = n.ieOffsetY || 0,
                    n.ieOffsetX = Math.round((p - ((s < 0 ? -s : s) * p + (a < 0 ? -a : a) * g)) / 2 + v),
                    n.ieOffsetY = Math.round((g - ((u < 0 ? -u : u) * g + (l < 0 ? -l : l) * p)) / 2 + y),
                    Ct = 0; Ct < 4; Ct++)
                        x = (i = -1 !== (w = h[b = at[Ct]]).indexOf("px") ? parseFloat(w) : it(this.t, b, parseFloat(w), w.replace(O, "")) || 0) !== n[b] ? Ct < 2 ? -n.ieOffsetX : -n.ieOffsetY : Ct < 2 ? f - n.ieOffsetX : d - n.ieOffsetY,
                        c[b] = (n[b] = Math.round(i - x * (0 === Ct || 2 === Ct ? 1 : T))) + "px"
                }
            }
        }, Zt = r.set3DTransformRatio = r.setTransformRatio = function(t) {
            var e, i, n, r, o, s, a, l, u, c, h, f, d, p, g, _, m, v, y, w, b = this.data, x = this.t.style, T = b.rotation, C = b.rotationX, k = b.rotationY, S = b.scaleX, $ = b.scaleY, D = b.scaleZ, A = b.x, E = b.y, P = b.z, O = b.svg, R = b.perspective, M = b.force3D, j = b.skewY, z = b.skewX;
            if (j && (z += j,
            T += j),
            !((1 !== t && 0 !== t || "auto" !== M || this.tween._totalTime !== this.tween._totalDuration && this.tween._totalTime) && M || P || R || k || C || 1 !== D) || Dt && O || !Ft)
                T || z || O ? (T *= I,
                w = z * I,
                1e5,
                i = Math.cos(T) * S,
                o = Math.sin(T) * S,
                n = Math.sin(T - w) * -$,
                s = Math.cos(T - w) * $,
                w && "simple" === b.skewType && (e = Math.tan(w - j * I),
                n *= e = Math.sqrt(1 + e * e),
                s *= e,
                j && (e = Math.tan(j * I),
                i *= e = Math.sqrt(1 + e * e),
                o *= e)),
                O && (A += b.xOrigin - (b.xOrigin * i + b.yOrigin * n) + b.xOffset,
                E += b.yOrigin - (b.xOrigin * o + b.yOrigin * s) + b.yOffset,
                Dt && (b.xPercent || b.yPercent) && (g = this.t.getBBox(),
                A += .01 * b.xPercent * g.width,
                E += .01 * b.yPercent * g.height),
                A < (g = 1e-6) && -g < A && (A = 0),
                E < g && -g < E && (E = 0)),
                y = (1e5 * i | 0) / 1e5 + "," + (1e5 * o | 0) / 1e5 + "," + (1e5 * n | 0) / 1e5 + "," + (1e5 * s | 0) / 1e5 + "," + A + "," + E + ")",
                O && Dt ? this.t.setAttribute("transform", "matrix(" + y) : x[Mt] = (b.xPercent || b.yPercent ? "translate(" + b.xPercent + "%," + b.yPercent + "%) matrix(" : "matrix(") + y) : x[Mt] = (b.xPercent || b.yPercent ? "translate(" + b.xPercent + "%," + b.yPercent + "%) matrix(" : "matrix(") + S + ",0,0," + $ + "," + A + "," + E + ")";
            else {
                if (F && (S < (g = 1e-4) && -g < S && (S = D = 2e-5),
                $ < g && -g < $ && ($ = D = 2e-5),
                !R || b.z || b.rotationX || b.rotationY || (R = 0)),
                T || z)
                    T *= I,
                    _ = i = Math.cos(T),
                    m = o = Math.sin(T),
                    z && (T -= z * I,
                    _ = Math.cos(T),
                    m = Math.sin(T),
                    "simple" === b.skewType && (e = Math.tan((z - j) * I),
                    _ *= e = Math.sqrt(1 + e * e),
                    m *= e,
                    b.skewY && (e = Math.tan(j * I),
                    i *= e = Math.sqrt(1 + e * e),
                    o *= e))),
                    n = -m,
                    s = _;
                else {
                    if (!(k || C || 1 !== D || R || O))
                        return void (x[Mt] = (b.xPercent || b.yPercent ? "translate(" + b.xPercent + "%," + b.yPercent + "%) translate3d(" : "translate3d(") + A + "px," + E + "px," + P + "px)" + (1 !== S || 1 !== $ ? " scale(" + S + "," + $ + ")" : ""));
                    i = s = 1,
                    n = o = 0
                }
                c = 1,
                r = a = l = u = h = f = 0,
                d = R ? -1 / R : 0,
                p = b.zOrigin,
                g = 1e-6,
                ",",
                "0",
                (T = k * I) && (_ = Math.cos(T),
                h = d * (l = -(m = Math.sin(T))),
                r = i * m,
                a = o * m,
                d *= c = _,
                i *= _,
                o *= _),
                (T = C * I) && (e = n * (_ = Math.cos(T)) + r * (m = Math.sin(T)),
                v = s * _ + a * m,
                u = c * m,
                f = d * m,
                r = n * -m + r * _,
                a = s * -m + a * _,
                c *= _,
                d *= _,
                n = e,
                s = v),
                1 !== D && (r *= D,
                a *= D,
                c *= D,
                d *= D),
                1 !== $ && (n *= $,
                s *= $,
                u *= $,
                f *= $),
                1 !== S && (i *= S,
                o *= S,
                l *= S,
                h *= S),
                (p || O) && (p && (A += r * -p,
                E += a * -p,
                P += c * -p + p),
                O && (A += b.xOrigin - (b.xOrigin * i + b.yOrigin * n) + b.xOffset,
                E += b.yOrigin - (b.xOrigin * o + b.yOrigin * s) + b.yOffset),
                A < g && -g < A && (A = "0"),
                E < g && -g < E && (E = "0"),
                P < g && -g < P && (P = 0)),
                y = b.xPercent || b.yPercent ? "translate(" + b.xPercent + "%," + b.yPercent + "%) matrix3d(" : "matrix3d(",
                y += (i < g && -g < i ? "0" : i) + "," + (o < g && -g < o ? "0" : o) + "," + (l < g && -g < l ? "0" : l),
                y += "," + (h < g && -g < h ? "0" : h) + "," + (n < g && -g < n ? "0" : n) + "," + (s < g && -g < s ? "0" : s),
                C || k || 1 !== D ? (y += "," + (u < g && -g < u ? "0" : u) + "," + (f < g && -g < f ? "0" : f) + "," + (r < g && -g < r ? "0" : r),
                y += "," + (a < g && -g < a ? "0" : a) + "," + (c < g && -g < c ? "0" : c) + "," + (d < g && -g < d ? "0" : d) + ",") : y += ",0,0,0,0,1,0,",
                y += A + "," + E + "," + P + "," + (R ? 1 + -P / R : 1) + ")",
                x[Mt] = y
            }
        }
        ;
        (t = It.prototype).x = t.y = t.z = t.skewX = t.skewY = t.rotation = t.rotationX = t.rotationY = t.zOrigin = t.xPercent = t.yPercent = t.xOffset = t.yOffset = 0,
        t.scaleX = t.scaleY = t.scaleZ = 1,
        St("transform,scale,scaleX,scaleY,scaleZ,x,y,z,rotation,rotationX,rotationY,rotationZ,skewX,skewY,shortRotation,shortRotationX,shortRotationY,shortRotationZ,transformOrigin,svgOrigin,transformPerspective,directionalRotation,parseTransform,force3D,skewType,xPercent,yPercent,smoothOrigin", {
            parser: function(t, e, i, n, r, o, s) {
                if (n._lastParsedTransform === s)
                    return r;
                var a, l = (n._lastParsedTransform = s).scale && "function" == typeof s.scale ? s.scale : 0;
                "function" == typeof s[i] && (a = s[i],
                s[i] = e),
                l && (s.scale = l(A, t));
                var u, c, h, f, d, p, g, _, m, v = t._gsTransform, y = t.style, w = Rt.length, b = s, x = {}, T = "transformOrigin", C = Gt(t, S, !0, b.parseTransform), k = b.transform && ("function" == typeof b.transform ? b.transform(A, D) : b.transform);
                if (C.skewType = b.skewType || C.skewType || L.defaultSkewType,
                n._transform = C,
                "rotationZ"in b && (b.rotation = b.rotationZ),
                k && "string" == typeof k && Mt)
                    (c = U.style)[Mt] = k,
                    c.display = "block",
                    c.position = "absolute",
                    -1 !== k.indexOf("%") && (c.width = et(t, "width"),
                    c.height = et(t, "height")),
                    W.body.appendChild(U),
                    u = Gt(U, null, !1),
                    "simple" === C.skewType && (u.scaleY *= Math.cos(u.skewX * I)),
                    C.svg && (p = C.xOrigin,
                    g = C.yOrigin,
                    u.x -= C.xOffset,
                    u.y -= C.yOffset,
                    (b.transformOrigin || b.svgOrigin) && (k = {},
                    Wt(t, ut(b.transformOrigin), k, b.svgOrigin, b.smoothOrigin, !0),
                    p = k.xOrigin,
                    g = k.yOrigin,
                    u.x -= k.xOffset - C.xOffset,
                    u.y -= k.yOffset - C.yOffset),
                    (p || g) && (_ = Xt(U, !0),
                    u.x -= p - (p * _[0] + g * _[2]),
                    u.y -= g - (p * _[1] + g * _[3]))),
                    W.body.removeChild(U),
                    u.perspective || (u.perspective = C.perspective),
                    null != b.xPercent && (u.xPercent = ht(b.xPercent, C.xPercent)),
                    null != b.yPercent && (u.yPercent = ht(b.yPercent, C.yPercent));
                else if ("object" == typeof b) {
                    if (u = {
                        scaleX: ht(null != b.scaleX ? b.scaleX : b.scale, C.scaleX),
                        scaleY: ht(null != b.scaleY ? b.scaleY : b.scale, C.scaleY),
                        scaleZ: ht(b.scaleZ, C.scaleZ),
                        x: ht(b.x, C.x),
                        y: ht(b.y, C.y),
                        z: ht(b.z, C.z),
                        xPercent: ht(b.xPercent, C.xPercent),
                        yPercent: ht(b.yPercent, C.yPercent),
                        perspective: ht(b.transformPerspective, C.perspective)
                    },
                    null != (d = b.directionalRotation))
                        if ("object" == typeof d)
                            for (c in d)
                                b[c] = d[c];
                        else
                            b.rotation = d;
                    "string" == typeof b.x && -1 !== b.x.indexOf("%") && (u.x = 0,
                    u.xPercent = ht(b.x, C.xPercent)),
                    "string" == typeof b.y && -1 !== b.y.indexOf("%") && (u.y = 0,
                    u.yPercent = ht(b.y, C.yPercent)),
                    u.rotation = ft("rotation"in b ? b.rotation : "shortRotation"in b ? b.shortRotation + "_short" : C.rotation, C.rotation, "rotation", x),
                    Ft && (u.rotationX = ft("rotationX"in b ? b.rotationX : "shortRotationX"in b ? b.shortRotationX + "_short" : C.rotationX || 0, C.rotationX, "rotationX", x),
                    u.rotationY = ft("rotationY"in b ? b.rotationY : "shortRotationY"in b ? b.shortRotationY + "_short" : C.rotationY || 0, C.rotationY, "rotationY", x)),
                    u.skewX = ft(b.skewX, C.skewX),
                    u.skewY = ft(b.skewY, C.skewY)
                }
                for (Ft && null != b.force3D && (C.force3D = b.force3D,
                f = !0),
                (h = C.force3D || C.z || C.rotationX || C.rotationY || u.z || u.rotationX || u.rotationY || u.perspective) || null == b.scale || (u.scaleZ = 1); -1 < --w; )
                    (1e-6 < (k = u[m = Rt[w]] - C[m]) || k < -1e-6 || null != b[m] || null != N[m]) && (f = !0,
                    r = new bt(C,m,C[m],k,r),
                    m in x && (r.e = x[m]),
                    r.xs0 = 0,
                    r.plugin = o,
                    n._overwriteProps.push(r.n));
                return k = b.transformOrigin,
                C.svg && (k || b.svgOrigin) && (p = C.xOffset,
                g = C.yOffset,
                Wt(t, ut(k), u, b.svgOrigin, b.smoothOrigin),
                r = xt(C, "xOrigin", (v ? C : u).xOrigin, u.xOrigin, r, T),
                r = xt(C, "yOrigin", (v ? C : u).yOrigin, u.yOrigin, r, T),
                (p !== C.xOffset || g !== C.yOffset) && (r = xt(C, "xOffset", v ? p : C.xOffset, C.xOffset, r, T),
                r = xt(C, "yOffset", v ? g : C.yOffset, C.yOffset, r, T)),
                k = "0px 0px"),
                (k || Ft && h && C.zOrigin) && (Mt ? (f = !0,
                m = zt,
                k = (k || et(t, m, S, !1, "50% 50%")) + "",
                (r = new bt(y,m,0,0,r,-1,T)).b = y[m],
                r.plugin = o,
                r.xs0 = r.e = Ft ? (c = C.zOrigin,
                k = k.split(" "),
                C.zOrigin = (2 < k.length && (0 === c || "0px" !== k[2]) ? parseFloat(k[2]) : c) || 0,
                r.xs0 = r.e = k[0] + " " + (k[1] || "50%") + " 0px",
                (r = new bt(C,"zOrigin",0,0,r,-1,r.n)).b = c,
                C.zOrigin) : k) : ut(k + "", C)),
                f && (n._transformType = C.svg && Dt || !h && 3 !== this._transformType ? 2 : 3),
                a && (s[i] = a),
                l && (s.scale = l),
                r
            },
            prefix: !0
        }),
        St("boxShadow", {
            defaultValue: "0px 0px 0px 0px #999",
            prefix: !0,
            color: !0,
            multi: !0,
            keyword: "inset"
        }),
        St("borderRadius", {
            defaultValue: "0px",
            parser: function(t, e, i, n, r, o) {
                e = this.format(e);
                var s, a, l, u, c, h, f, d, p, g, _, m, v, y, w, b, x = ["borderTopLeftRadius", "borderTopRightRadius", "borderBottomRightRadius", "borderBottomLeftRadius"], T = t.style;
                for (p = parseFloat(t.offsetWidth),
                g = parseFloat(t.offsetHeight),
                s = e.split(" "),
                a = 0; a < x.length; a++)
                    this.p.indexOf("border") && (x[a] = J(x[a])),
                    -1 !== (c = u = et(t, x[a], S, !1, "0px")).indexOf(" ") && (c = (u = c.split(" "))[0],
                    u = u[1]),
                    h = l = s[a],
                    f = parseFloat(c),
                    m = c.substr((f + "").length),
                    "" === (_ = (v = "=" === h.charAt(1)) ? (d = parseInt(h.charAt(0) + "1", 10),
                    h = h.substr(2),
                    d *= parseFloat(h),
                    h.substr((d + "").length - (d < 0 ? 1 : 0)) || "") : (d = parseFloat(h),
                    h.substr((d + "").length))) && (_ = C[i] || m),
                    _ !== m && (y = it(t, "borderLeft", f, m),
                    w = it(t, "borderTop", f, m),
                    u = "%" === _ ? (c = y / p * 100 + "%",
                    w / g * 100 + "%") : "em" === _ ? (c = y / (b = it(t, "borderLeft", 1, "em")) + "em",
                    w / b + "em") : (c = y + "px",
                    w + "px"),
                    v && (h = parseFloat(c) + d + _,
                    l = parseFloat(u) + d + _)),
                    r = Tt(T, x[a], c + " " + u, h + " " + l, !1, "0px", r);
                return r
            },
            prefix: !0,
            formatter: vt("0px 0px 0px 0px", !1, !0)
        }),
        St("borderBottomLeftRadius,borderBottomRightRadius,borderTopLeftRadius,borderTopRightRadius", {
            defaultValue: "0px",
            parser: function(t, e, i, n, r, o) {
                return Tt(t.style, i, this.format(et(t, i, S, !1, "0px 0px")), this.format(e), !1, "0px", r)
            },
            prefix: !0,
            formatter: vt("0px 0px", !1, !0)
        }),
        St("backgroundPosition", {
            defaultValue: "0 0",
            parser: function(t, e, i, n, r, o) {
                var s, a, l, u, c, h, f = "background-position", d = S || tt(t, null), p = this.format((d ? k ? d.getPropertyValue(f + "-x") + " " + d.getPropertyValue(f + "-y") : d.getPropertyValue(f) : t.currentStyle.backgroundPositionX + " " + t.currentStyle.backgroundPositionY) || "0 0"), g = this.format(e);
                if (-1 !== p.indexOf("%") != (-1 !== g.indexOf("%")) && g.split(",").length < 2 && ((h = et(t, "backgroundImage").replace(T, "")) && "none" !== h)) {
                    for (s = p.split(" "),
                    a = g.split(" "),
                    V.setAttribute("src", h),
                    l = 2; -1 < --l; )
                        (u = -1 !== (p = s[l]).indexOf("%")) !== (-1 !== a[l].indexOf("%")) && (c = 0 === l ? t.offsetWidth - V.width : t.offsetHeight - V.height,
                        s[l] = u ? parseFloat(p) / 100 * c + "px" : parseFloat(p) / c * 100 + "%");
                    p = s.join(" ")
                }
                return this.parseComplex(t.style, p, g, r, o)
            },
            formatter: ut
        }),
        St("backgroundSize", {
            defaultValue: "0 0",
            formatter: function(t) {
                return "co" === (t += "").substr(0, 2) ? t : ut(-1 === t.indexOf(" ") ? t + " " + t : t)
            }
        }),
        St("perspective", {
            defaultValue: "0px",
            prefix: !0
        }),
        St("perspectiveOrigin", {
            defaultValue: "50% 50%",
            prefix: !0
        }),
        St("transformStyle", {
            prefix: !0
        }),
        St("backfaceVisibility", {
            prefix: !0
        }),
        St("userSelect", {
            prefix: !0
        }),
        St("margin", {
            parser: yt("marginTop,marginRight,marginBottom,marginLeft")
        }),
        St("padding", {
            parser: yt("paddingTop,paddingRight,paddingBottom,paddingLeft")
        }),
        St("clip", {
            defaultValue: "rect(0px,0px,0px,0px)",
            parser: function(t, e, i, n, r, o) {
                var s, a, l;
                return e = k < 9 ? (a = t.currentStyle,
                l = k < 8 ? " " : ",",
                s = "rect(" + a.clipTop + l + a.clipRight + l + a.clipBottom + l + a.clipLeft + ")",
                this.format(e).split(",").join(l)) : (s = this.format(et(t, this.p, S, !1, this.dflt)),
                this.format(e)),
                this.parseComplex(t.style, s, e, r, o)
            }
        }),
        St("textShadow", {
            defaultValue: "0px 0px 0px #999",
            color: !0,
            multi: !0
        }),
        St("autoRound,strictUnits", {
            parser: function(t, e, i, n, r) {
                return r
            }
        }),
        St("border", {
            defaultValue: "0px solid #000",
            parser: function(t, e, i, n, r, o) {
                var s = et(t, "borderTopWidth", S, !1, "0px")
                  , a = this.format(e).split(" ")
                  , l = a[0].replace(O, "");
                return "px" !== l && (s = parseFloat(s) / it(t, "borderTopWidth", 1, l) + l),
                this.parseComplex(t.style, this.format(s + " " + et(t, "borderTopStyle", S, !1, "solid") + " " + et(t, "borderTopColor", S, !1, "#000")), a.join(" "), r, o)
            },
            color: !0,
            formatter: function(t) {
                var e = t.split(" ");
                return e[0] + " " + (e[1] || "solid") + " " + (t.match(mt) || ["#000"])[0]
            }
        }),
        St("borderWidth", {
            parser: yt("borderTopWidth,borderRightWidth,borderBottomWidth,borderLeftWidth")
        }),
        St("float,cssFloat,styleFloat", {
            parser: function(t, e, i, n, r, o) {
                var s = t.style
                  , a = "cssFloat"in s ? "cssFloat" : "styleFloat";
                return new bt(s,a,0,0,r,-1,i,!1,0,s[a],e)
            }
        });
        var Qt = function(t) {
            var e, i = this.t, n = i.filter || et(this.data, "filter") || "", r = this.s + this.c * t | 0;
            100 === r && (e = -1 === n.indexOf("atrix(") && -1 === n.indexOf("radient(") && -1 === n.indexOf("oader(") ? (i.removeAttribute("filter"),
            !et(this.data, "filter")) : (i.filter = n.replace(s, ""),
            !0)),
            e || (this.xn1 && (i.filter = n = n || "alpha(opacity=" + r + ")"),
            -1 === n.indexOf("pacity") ? 0 === r && this.xn1 || (i.filter = n + " alpha(opacity=" + r + ")") : i.filter = n.replace(R, "opacity=" + r))
        };
        St("opacity,alpha,autoAlpha", {
            defaultValue: "1",
            parser: function(t, e, i, n, r, o) {
                var s = parseFloat(et(t, "opacity", S, !1, "1"))
                  , a = t.style
                  , l = "autoAlpha" === i;
                return "string" == typeof e && "=" === e.charAt(1) && (e = ("-" === e.charAt(0) ? -1 : 1) * parseFloat(e.substr(2)) + s),
                l && 1 === s && "hidden" === et(t, "visibility", S) && 0 !== e && (s = 0),
                G ? r = new bt(a,"opacity",s,e - s,r) : ((r = new bt(a,"opacity",100 * s,100 * (e - s),r)).xn1 = l ? 1 : 0,
                a.zoom = 1,
                r.type = 2,
                r.b = "alpha(opacity=" + r.s + ")",
                r.e = "alpha(opacity=" + (r.s + r.c) + ")",
                r.data = t,
                r.plugin = o,
                r.setRatio = Qt),
                l && ((r = new bt(a,"visibility",0,0,r,-1,null,!1,0,0 !== s ? "inherit" : "hidden",0 === e ? "hidden" : "inherit")).xs0 = "inherit",
                n._overwriteProps.push(r.n),
                n._overwriteProps.push(i)),
                r
            }
        });
        var Kt = function(t, e) {
            e && (t.removeProperty ? (("ms" === e.substr(0, 2) || "webkit" === e.substr(0, 6)) && (e = "-" + e),
            t.removeProperty(e.replace(a, "-$1").toLowerCase())) : t.removeAttribute(e))
        }
          , Jt = function(t) {
            if (this.t._gsClassPT = this,
            1 === t || 0 === t) {
                this.t.setAttribute("class", 0 === t ? this.b : this.e);
                for (var e = this.data, i = this.t.style; e; )
                    e.v ? i[e.p] = e.v : Kt(i, e.p),
                    e = e._next;
                1 === t && this.t._gsClassPT === this && (this.t._gsClassPT = null)
            } else
                this.t.getAttribute("class") !== this.e && this.t.setAttribute("class", this.e)
        };
        St("className", {
            parser: function(t, e, i, n, r, o, s) {
                var a, l, u, c, h, f = t.getAttribute("class") || "", d = t.style.cssText;
                if ((r = n._classNamePT = new bt(t,i,0,0,r,2)).setRatio = Jt,
                r.pr = -11,
                p = !0,
                r.b = f,
                l = rt(t, S),
                u = t._gsClassPT) {
                    for (c = {},
                    h = u.data; h; )
                        c[h.p] = 1,
                        h = h._next;
                    u.setRatio(1)
                }
                return (t._gsClassPT = r).e = "=" !== e.charAt(1) ? e : f.replace(new RegExp("(?:\\s|^)" + e.substr(2) + "(?![\\w-])"), "") + ("+" === e.charAt(0) ? " " + e.substr(2) : ""),
                t.setAttribute("class", r.e),
                a = ot(t, l, rt(t), s, c),
                t.setAttribute("class", f),
                r.data = a.firstMPT,
                t.style.cssText = d,
                r.xfirst = n.parse(t, a.difs, r, o)
            }
        });
        var te = function(t) {
            if ((1 === t || 0 === t) && this.data._totalTime === this.data._totalDuration && "isFromStart" !== this.data.data) {
                var e, i, n, r, o, s = this.t.style, a = _.transform.parse;
                if ("all" === this.e)
                    r = !(s.cssText = "");
                else
                    for (n = (e = this.e.split(" ").join("").split(",")).length; -1 < --n; )
                        i = e[n],
                        _[i] && (_[i].parse === a ? r = !0 : i = "transformOrigin" === i ? zt : _[i].p),
                        Kt(s, i);
                r && (Kt(s, Mt),
                (o = this.t._gsTransform) && (o.svg && (this.t.removeAttribute("data-svg-origin"),
                this.t.removeAttribute("transform")),
                delete this.t._gsTransform))
            }
        };
        for (St("clearProps", {
            parser: function(t, e, i, n, r) {
                return (r = new bt(t,i,0,0,r,2)).setRatio = te,
                r.e = e,
                r.pr = -10,
                r.data = n._tween,
                p = !0,
                r
            }
        }),
        t = "bezier,throwProps,physicsProps,physics2D".split(","),
        Ct = t.length; Ct--; )
            $t(t[Ct]);
        (t = L.prototype)._firstPT = t._lastParsedTransform = t._transform = null,
        t._onInitTween = function(t, e, i, n) {
            if (!t.nodeType)
                return !1;
            this._target = D = t,
            this._tween = i,
            this._vars = e,
            A = n,
            $ = e.autoRound,
            p = !1,
            C = e.suffixMap || L.suffixMap,
            S = tt(t, ""),
            g = this._overwriteProps;
            var r, o, s, a, l, u, c, h, f, d = t.style;
            if (m && "" === d.zIndex && (("auto" === (r = et(t, "zIndex", S)) || "" === r) && this._addLazySet(d, "zIndex", 0)),
            "string" == typeof e && (a = d.cssText,
            r = rt(t, S),
            d.cssText = a + ";" + e,
            r = ot(t, r, rt(t)).difs,
            !G && b.test(e) && (r.opacity = parseFloat(RegExp.$1)),
            e = r,
            d.cssText = a),
            e.className ? this._firstPT = o = _.className.parse(t, e.className, "className", this, null, null, e) : this._firstPT = o = this.parse(t, e, null),
            this._transformType) {
                for (f = 3 === this._transformType,
                Mt ? v && (m = !0,
                "" === d.zIndex && (("auto" === (c = et(t, "zIndex", S)) || "" === c) && this._addLazySet(d, "zIndex", 0)),
                y && this._addLazySet(d, "WebkitBackfaceVisibility", this._vars.WebkitBackfaceVisibility || (f ? "visible" : "hidden"))) : d.zoom = 1,
                s = o; s && s._next; )
                    s = s._next;
                h = new bt(t,"transform",0,0,null,2),
                this._linkCSSP(h, null, s),
                h.setRatio = Mt ? Zt : Yt,
                h.data = this._transform || Gt(t, S, !0),
                h.tween = i,
                h.pr = -1,
                g.pop()
            }
            if (p) {
                for (; o; ) {
                    for (u = o._next,
                    s = a; s && s.pr > o.pr; )
                        s = s._next;
                    (o._prev = s ? s._prev : l) ? o._prev._next = o : a = o,
                    (o._next = s) ? s._prev = o : l = o,
                    o = u
                }
                this._firstPT = a
            }
            return !0
        }
        ,
        t.parse = function(t, e, i, n) {
            var r, o, s, a, l, u, c, h, f, d, p = t.style;
            for (r in e) {
                if ("function" == typeof (u = e[r]) && (u = u(A, D)),
                o = _[r])
                    i = o.parse(t, u, r, this, i, n, e);
                else {
                    if ("--" === r.substr(0, 2)) {
                        this._tween._propLookup[r] = this._addTween.call(this._tween, t.style, "setProperty", tt(t).getPropertyValue(r) + "", u + "", r, !1, r);
                        continue
                    }
                    l = et(t, r, S) + "",
                    f = "string" == typeof u,
                    "color" === r || "fill" === r || "stroke" === r || -1 !== r.indexOf("Color") || f && x.test(u) ? (f || (u = (3 < (u = gt(u)).length ? "rgba(" : "rgb(") + u.join(",") + ")"),
                    i = Tt(p, r, l, u, !0, "transparent", i, 0, n)) : f && z.test(u) ? i = Tt(p, r, l, u, !0, null, i, 0, n) : (c = (s = parseFloat(l)) || 0 === s ? l.substr((s + "").length) : "",
                    ("" === l || "auto" === l) && (c = "width" === r || "height" === r ? (s = lt(t, r, S),
                    "px") : "left" === r || "top" === r ? (s = nt(t, r, S),
                    "px") : (s = "opacity" !== r ? 0 : 1,
                    "")),
                    "" === (h = (d = f && "=" === u.charAt(1)) ? (a = parseInt(u.charAt(0) + "1", 10),
                    u = u.substr(2),
                    a *= parseFloat(u),
                    u.replace(O, "")) : (a = parseFloat(u),
                    f ? u.replace(O, "") : "")) && (h = r in C ? C[r] : c),
                    u = a || 0 === a ? (d ? a + s : a) + h : e[r],
                    c !== h && ("" !== h || "lineHeight" === r) && (a || 0 === a) && s && (s = it(t, r, s, c),
                    "%" === h ? (s /= it(t, r, 100, "%") / 100,
                    !0 !== e.strictUnits && (l = s + "%")) : "em" === h || "rem" === h || "vw" === h || "vh" === h ? s /= it(t, r, 1, h) : "px" !== h && (a = it(t, r, a, h),
                    h = "px"),
                    d && (a || 0 === a) && (u = a + s + h)),
                    d && (a += s),
                    !s && 0 !== s || !a && 0 !== a ? void 0 !== p[r] && (u || u + "" != "NaN" && null != u) ? (i = new bt(p,r,a || s || 0,0,i,-1,r,!1,0,l,u)).xs0 = "none" !== u || "display" !== r && -1 === r.indexOf("Style") ? u : l : Z("invalid " + r + " tween value: " + e[r]) : (i = new bt(p,r,s,a - s,i,0,r,!1 !== $ && ("px" === h || "zIndex" === r),0,l,u)).xs0 = h)
                }
                n && i && !i.plugin && (i.plugin = n)
            }
            return i
        }
        ,
        t.setRatio = function(t) {
            var e, i, n, r = this._firstPT;
            if (1 !== t || this._tween._time !== this._tween._duration && 0 !== this._tween._time)
                if (t || this._tween._time !== this._tween._duration && 0 !== this._tween._time || -1e-6 === this._tween._rawPrevTime)
                    for (; r; ) {
                        if (e = r.c * t + r.s,
                        r.r ? e = r.r(e) : e < 1e-6 && -1e-6 < e && (e = 0),
                        r.type)
                            if (1 === r.type)
                                if (2 === (n = r.l))
                                    r.t[r.p] = r.xs0 + e + r.xs1 + r.xn1 + r.xs2;
                                else if (3 === n)
                                    r.t[r.p] = r.xs0 + e + r.xs1 + r.xn1 + r.xs2 + r.xn2 + r.xs3;
                                else if (4 === n)
                                    r.t[r.p] = r.xs0 + e + r.xs1 + r.xn1 + r.xs2 + r.xn2 + r.xs3 + r.xn3 + r.xs4;
                                else if (5 === n)
                                    r.t[r.p] = r.xs0 + e + r.xs1 + r.xn1 + r.xs2 + r.xn2 + r.xs3 + r.xn3 + r.xs4 + r.xn4 + r.xs5;
                                else {
                                    for (i = r.xs0 + e + r.xs1,
                                    n = 1; n < r.l; n++)
                                        i += r["xn" + n] + r["xs" + (n + 1)];
                                    r.t[r.p] = i
                                }
                            else
                                -1 === r.type ? r.t[r.p] = r.xs0 : r.setRatio && r.setRatio(t);
                        else
                            r.t[r.p] = e + r.xs0;
                        r = r._next
                    }
                else
                    for (; r; )
                        2 !== r.type ? r.t[r.p] = r.b : r.setRatio(t),
                        r = r._next;
            else
                for (; r; ) {
                    if (2 !== r.type)
                        if (r.r && -1 !== r.type)
                            if (e = r.r(r.s + r.c),
                            r.type) {
                                if (1 === r.type) {
                                    for (n = r.l,
                                    i = r.xs0 + e + r.xs1,
                                    n = 1; n < r.l; n++)
                                        i += r["xn" + n] + r["xs" + (n + 1)];
                                    r.t[r.p] = i
                                }
                            } else
                                r.t[r.p] = e + r.xs0;
                        else
                            r.t[r.p] = r.e;
                    else
                        r.setRatio(t);
                    r = r._next
                }
        }
        ,
        t._enableTransforms = function(t) {
            this._transform = this._transform || Gt(this._target, S, !0),
            this._transformType = this._transform.svg && Dt || !t && 3 !== this._transformType ? 2 : 3
        }
        ;
        var ee = function(t) {
            this.t[this.p] = this.e,
            this.data._linkCSSP(this, this._next, null, !0)
        };
        t._addLazySet = function(t, e, i) {
            var n = this._firstPT = new bt(t,e,0,0,this._firstPT,2);
            n.e = i,
            n.setRatio = ee,
            n.data = this
        }
        ,
        t._linkCSSP = function(t, e, i, n) {
            return t && (e && (e._prev = t),
            t._next && (t._next._prev = t._prev),
            t._prev ? t._prev._next = t._next : this._firstPT === t && (this._firstPT = t._next,
            n = !0),
            i ? i._next = t : n || null !== this._firstPT || (this._firstPT = t),
            t._next = e,
            t._prev = i),
            t
        }
        ,
        t._mod = function(t) {
            for (var e = this._firstPT; e; )
                "function" == typeof t[e.p] && (e.r = t[e.p]),
                e = e._next
        }
        ,
        t._kill = function(t) {
            var e, i, n, r = t;
            if (t.autoAlpha || t.alpha) {
                for (i in r = {},
                t)
                    r[i] = t[i];
                r.opacity = 1,
                r.autoAlpha && (r.visibility = 1)
            }
            for (t.className && (e = this._classNamePT) && ((n = e.xfirst) && n._prev ? this._linkCSSP(n._prev, e._next, n._prev._prev) : n === this._firstPT && (this._firstPT = e._next),
            e._next && this._linkCSSP(e._next, e._next._next, n._prev),
            this._classNamePT = null),
            e = this._firstPT; e; )
                e.plugin && e.plugin !== i && e.plugin._kill && (e.plugin._kill(t),
                i = e.plugin),
                e = e._next;
            return o.prototype._kill.call(this, r)
        }
        ;
        var ie = function(t, e, i) {
            var n, r, o, s;
            if (t.slice)
                for (r = t.length; -1 < --r; )
                    ie(t[r], e, i);
            else
                for (r = (n = t.childNodes).length; -1 < --r; )
                    s = (o = n[r]).type,
                    o.style && (e.push(rt(o)),
                    i && i.push(o)),
                    1 !== s && 9 !== s && 11 !== s || !o.childNodes.length || ie(o, e, i)
        };
        return L.cascadeTo = function(t, e, i) {
            var n, r, o, s, a = B.to(t, e, i), l = [a], u = [], c = [], h = [], f = B._internals.reservedProps;
            for (t = a._targets || a.target,
            ie(t, u, h),
            a.render(e, !0, !0),
            ie(t, c),
            a.render(0, !0, !0),
            a._enabled(!0),
            n = h.length; -1 < --n; )
                if ((r = ot(h[n], u[n], c[n])).firstMPT) {
                    for (o in r = r.difs,
                    i)
                        f[o] && (r[o] = i[o]);
                    for (o in s = {},
                    r)
                        s[o] = u[n][o];
                    l.push(B.fromTo(h[n], e, s, r))
                }
            return l
        }
        ,
        o.activate([L]),
        L
    }, !0),
    t = _gsScope._gsDefine.plugin({
        propName: "roundProps",
        version: "1.7.0",
        priority: -1,
        API: 2,
        init: function(t, e, i) {
            return this._tween = i,
            !0
        }
    }),
    l = function(e) {
        var i = e < 1 ? Math.pow(10, (e + "").length - 2) : 1;
        return function(t) {
            return (Math.round(t / e) * e * i | 0) / i
        }
    }
    ,
    u = function(t, e) {
        for (; t; )
            t.f || t.blob || (t.m = e || Math.round),
            t = t._next
    }
    ,
    (e = t.prototype)._onInitAllProps = function() {
        var t, e, i, n, r = this._tween, o = r.vars.roundProps, s = {}, a = r._propLookup.roundProps;
        if ("object" != typeof o || o.push)
            for ("string" == typeof o && (o = o.split(",")),
            i = o.length; -1 < --i; )
                s[o[i]] = Math.round;
        else
            for (n in o)
                s[n] = l(o[n]);
        for (n in s)
            for (t = r._firstPT; t; )
                e = t._next,
                t.pg ? t.t._mod(s) : t.n === n && (2 === t.f && t.t ? u(t.t._firstPT, s[n]) : (this._add(t.t, n, t.s, t.c, s[n]),
                e && (e._prev = t._prev),
                t._prev ? t._prev._next = e : r._firstPT === t && (r._firstPT = e),
                t._next = t._prev = null,
                r._propLookup[n] = a)),
                t = e;
        return !1
    }
    ,
    e._add = function(t, e, i, n, r) {
        this._addTween(t, e, i, i + n, e, r || Math.round),
        this._overwriteProps.push(e)
    }
    ,
    _gsScope._gsDefine.plugin({
        propName: "attr",
        API: 2,
        version: "0.6.1",
        init: function(t, e, i, n) {
            var r, o;
            if ("function" != typeof t.setAttribute)
                return !1;
            for (r in e)
                "function" == typeof (o = e[r]) && (o = o(n, t)),
                this._addTween(t, "setAttribute", t.getAttribute(r) + "", o + "", r, !1, r),
                this._overwriteProps.push(r);
            return !0
        }
    }),
    _gsScope._gsDefine.plugin({
        propName: "directionalRotation",
        version: "0.3.1",
        API: 2,
        init: function(t, e, i, n) {
            "object" != typeof e && (e = {
                rotation: e
            }),
            this.finals = {};
            var r, o, s, a, l, u, c = !0 === e.useRadians ? 2 * Math.PI : 360;
            for (r in e)
                "useRadians" !== r && ("function" == typeof (a = e[r]) && (a = a(n, t)),
                o = (u = (a + "").split("_"))[0],
                s = parseFloat("function" != typeof t[r] ? t[r] : t[r.indexOf("set") || "function" != typeof t["get" + r.substr(3)] ? r : "get" + r.substr(3)]()),
                l = (a = this.finals[r] = "string" == typeof o && "=" === o.charAt(1) ? s + parseInt(o.charAt(0) + "1", 10) * Number(o.substr(2)) : Number(o) || 0) - s,
                u.length && (-1 !== (o = u.join("_")).indexOf("short") && ((l %= c) !== l % (c / 2) && (l = l < 0 ? l + c : l - c)),
                -1 !== o.indexOf("_cw") && l < 0 ? l = (l + 9999999999 * c) % c - (l / c | 0) * c : -1 !== o.indexOf("ccw") && 0 < l && (l = (l - 9999999999 * c) % c - (l / c | 0) * c)),
                (1e-6 < l || l < -1e-6) && (this._addTween(t, r, s, s + l, r),
                this._overwriteProps.push(r)));
            return !0
        },
        set: function(t) {
            var e;
            if (1 !== t)
                this._super.setRatio.call(this, t);
            else
                for (e = this._firstPT; e; )
                    e.f ? e.t[e.p](this.finals[e.p]) : e.t[e.p] = this.finals[e.p],
                    e = e._next
        }
    })._autoCSS = !0,
    _gsScope._gsDefine("easing.Back", ["easing.Ease"], function(_) {
        var i, n, e, t, r = _gsScope.GreenSockGlobals || _gsScope, o = r.com.greensock, s = 2 * Math.PI, a = Math.PI / 2, l = o._class, u = function(t, e) {
            var i = l("easing." + t, function() {}, !0)
              , n = i.prototype = new _;
            return n.constructor = i,
            n.getRatio = e,
            i
        }, c = _.register || function() {}
        , h = function(t, e, i, n, r) {
            var o = l("easing." + t, {
                easeOut: new e,
                easeIn: new i,
                easeInOut: new n
            }, !0);
            return c(o, t),
            o
        }, m = function(t, e, i) {
            this.t = t,
            this.v = e,
            i && (((this.next = i).prev = this).c = i.v - e,
            this.gap = i.t - t)
        }, f = function(t, e) {
            var i = l("easing." + t, function(t) {
                this._p1 = t || 0 === t ? t : 1.70158,
                this._p2 = 1.525 * this._p1
            }, !0)
              , n = i.prototype = new _;
            return n.constructor = i,
            n.getRatio = e,
            n.config = function(t) {
                return new i(t)
            }
            ,
            i
        }, d = h("Back", f("BackOut", function(t) {
            return (t -= 1) * t * ((this._p1 + 1) * t + this._p1) + 1
        }), f("BackIn", function(t) {
            return t * t * ((this._p1 + 1) * t - this._p1)
        }), f("BackInOut", function(t) {
            return (t *= 2) < 1 ? .5 * t * t * ((this._p2 + 1) * t - this._p2) : .5 * ((t -= 2) * t * ((this._p2 + 1) * t + this._p2) + 2)
        })), p = l("easing.SlowMo", function(t, e, i) {
            e = e || 0 === e ? e : .7,
            null == t ? t = .7 : 1 < t && (t = 1),
            this._p = 1 !== t ? e : 0,
            this._p1 = (1 - t) / 2,
            this._p2 = t,
            this._p3 = this._p1 + this._p2,
            this._calcEnd = !0 === i
        }, !0), g = p.prototype = new _;
        return g.constructor = p,
        g.getRatio = function(t) {
            var e = t + (.5 - t) * this._p;
            return t < this._p1 ? this._calcEnd ? 1 - (t = 1 - t / this._p1) * t : e - (t = 1 - t / this._p1) * t * t * t * e : t > this._p3 ? this._calcEnd ? 1 === t ? 0 : 1 - (t = (t - this._p3) / this._p1) * t : e + (t - e) * (t = (t - this._p3) / this._p1) * t * t * t : this._calcEnd ? 1 : e
        }
        ,
        p.ease = new p(.7,.7),
        g.config = p.config = function(t, e, i) {
            return new p(t,e,i)
        }
        ,
        (g = (i = l("easing.SteppedEase", function(t, e) {
            t = t || 1,
            this._p1 = 1 / t,
            this._p2 = t + (e ? 0 : 1),
            this._p3 = e ? 1 : 0
        }, !0)).prototype = new _).constructor = i,
        g.getRatio = function(t) {
            return t < 0 ? t = 0 : 1 <= t && (t = .999999999),
            ((this._p2 * t | 0) + this._p3) * this._p1
        }
        ,
        g.config = i.config = function(t, e) {
            return new i(t,e)
        }
        ,
        (g = (n = l("easing.ExpoScaleEase", function(t, e, i) {
            this._p1 = Math.log(e / t),
            this._p2 = e - t,
            this._p3 = t,
            this._ease = i
        }, !0)).prototype = new _).constructor = n,
        g.getRatio = function(t) {
            return this._ease && (t = this._ease.getRatio(t)),
            (this._p3 * Math.exp(this._p1 * t) - this._p3) / this._p2
        }
        ,
        g.config = n.config = function(t, e, i) {
            return new n(t,e,i)
        }
        ,
        (g = (e = l("easing.RoughEase", function(t) {
            for (var e, i, n, r, o, s, a = (t = t || {}).taper || "none", l = [], u = 0, c = 0 | (t.points || 20), h = c, f = !1 !== t.randomize, d = !0 === t.clamp, p = t.template instanceof _ ? t.template : null, g = "number" == typeof t.strength ? .4 * t.strength : .4; -1 < --h; )
                e = f ? Math.random() : 1 / c * h,
                i = p ? p.getRatio(e) : e,
                n = "none" === a ? g : "out" === a ? (r = 1 - e) * r * g : "in" === a ? e * e * g : (r = e < .5 ? 2 * e : 2 * (1 - e)) * r * .5 * g,
                f ? i += Math.random() * n - .5 * n : h % 2 ? i += .5 * n : i -= .5 * n,
                d && (1 < i ? i = 1 : i < 0 && (i = 0)),
                l[u++] = {
                    x: e,
                    y: i
                };
            for (l.sort(function(t, e) {
                return t.x - e.x
            }),
            s = new m(1,1,null),
            h = c; -1 < --h; )
                o = l[h],
                s = new m(o.x,o.y,s);
            this._prev = new m(0,0,0 !== s.t ? s : s.next)
        }, !0)).prototype = new _).constructor = e,
        g.getRatio = function(t) {
            var e = this._prev;
            if (t > e.t) {
                for (; e.next && t >= e.t; )
                    e = e.next;
                e = e.prev
            } else
                for (; e.prev && t <= e.t; )
                    e = e.prev;
            return (this._prev = e).v + (t - e.t) / e.gap * e.c
        }
        ,
        g.config = function(t) {
            return new e(t)
        }
        ,
        e.ease = new e,
        h("Bounce", u("BounceOut", function(t) {
            return t < 1 / 2.75 ? 7.5625 * t * t : t < 2 / 2.75 ? 7.5625 * (t -= 1.5 / 2.75) * t + .75 : t < 2.5 / 2.75 ? 7.5625 * (t -= 2.25 / 2.75) * t + .9375 : 7.5625 * (t -= 2.625 / 2.75) * t + .984375
        }), u("BounceIn", function(t) {
            return (t = 1 - t) < 1 / 2.75 ? 1 - 7.5625 * t * t : t < 2 / 2.75 ? 1 - (7.5625 * (t -= 1.5 / 2.75) * t + .75) : t < 2.5 / 2.75 ? 1 - (7.5625 * (t -= 2.25 / 2.75) * t + .9375) : 1 - (7.5625 * (t -= 2.625 / 2.75) * t + .984375)
        }), u("BounceInOut", function(t) {
            var e = t < .5;
            return t = (t = e ? 1 - 2 * t : 2 * t - 1) < 1 / 2.75 ? 7.5625 * t * t : t < 2 / 2.75 ? 7.5625 * (t -= 1.5 / 2.75) * t + .75 : t < 2.5 / 2.75 ? 7.5625 * (t -= 2.25 / 2.75) * t + .9375 : 7.5625 * (t -= 2.625 / 2.75) * t + .984375,
            e ? .5 * (1 - t) : .5 * t + .5
        })),
        h("Circ", u("CircOut", function(t) {
            return Math.sqrt(1 - (t -= 1) * t)
        }), u("CircIn", function(t) {
            return -(Math.sqrt(1 - t * t) - 1)
        }), u("CircInOut", function(t) {
            return (t *= 2) < 1 ? -.5 * (Math.sqrt(1 - t * t) - 1) : .5 * (Math.sqrt(1 - (t -= 2) * t) + 1)
        })),
        h("Elastic", (t = function(t, e, i) {
            var n = l("easing." + t, function(t, e) {
                this._p1 = 1 <= t ? t : 1,
                this._p2 = (e || i) / (t < 1 ? t : 1),
                this._p3 = this._p2 / s * (Math.asin(1 / this._p1) || 0),
                this._p2 = s / this._p2
            }, !0)
              , r = n.prototype = new _;
            return r.constructor = n,
            r.getRatio = e,
            r.config = function(t, e) {
                return new n(t,e)
            }
            ,
            n
        }
        )("ElasticOut", function(t) {
            return this._p1 * Math.pow(2, -10 * t) * Math.sin((t - this._p3) * this._p2) + 1
        }, .3), t("ElasticIn", function(t) {
            return -this._p1 * Math.pow(2, 10 * (t -= 1)) * Math.sin((t - this._p3) * this._p2)
        }, .3), t("ElasticInOut", function(t) {
            return (t *= 2) < 1 ? this._p1 * Math.pow(2, 10 * (t -= 1)) * Math.sin((t - this._p3) * this._p2) * -.5 : this._p1 * Math.pow(2, -10 * (t -= 1)) * Math.sin((t - this._p3) * this._p2) * .5 + 1
        }, .45)),
        h("Expo", u("ExpoOut", function(t) {
            return 1 - Math.pow(2, -10 * t)
        }), u("ExpoIn", function(t) {
            return Math.pow(2, 10 * (t - 1)) - .001
        }), u("ExpoInOut", function(t) {
            return (t *= 2) < 1 ? .5 * Math.pow(2, 10 * (t - 1)) : .5 * (2 - Math.pow(2, -10 * (t - 1)))
        })),
        h("Sine", u("SineOut", function(t) {
            return Math.sin(t * a)
        }), u("SineIn", function(t) {
            return 1 - Math.cos(t * a)
        }), u("SineInOut", function(t) {
            return -.5 * (Math.cos(Math.PI * t) - 1)
        })),
        l("easing.EaseLookup", {
            find: function(t) {
                return _.map[t]
            }
        }, !0),
        c(r.SlowMo, "SlowMo", "ease,"),
        c(e, "RoughEase", "ease,"),
        c(i, "SteppedEase", "ease,"),
        d
    }, !0)
}),
_gsScope._gsDefine && _gsScope._gsQueue.pop()(),
function(f, d) {
    "use strict";
    var p = {}
      , n = f.document
      , g = f.GreenSockGlobals = f.GreenSockGlobals || f
      , t = g[d];
    if (t)
        return "undefined" != typeof module && module.exports && (module.exports = t);
    var e, i, r, _, m, o, s, v = function(t) {
        var e, i = t.split("."), n = g;
        for (e = 0; e < i.length; e++)
            n[i[e]] = n = n[i[e]] || {};
        return n
    }, h = v("com.greensock"), y = 1e-10, l = function(t) {
        var e, i = [], n = t.length;
        for (e = 0; e !== n; i.push(t[e++]))
            ;
        return i
    }, w = function() {}, b = (o = Object.prototype.toString,
    s = o.call([]),
    function(t) {
        return null != t && (t instanceof Array || "object" == typeof t && !!t.push && o.call(t) === s)
    }
    ), x = {}, T = function(a, l, u, c) {
        this.sc = x[a] ? x[a].sc : [],
        (x[a] = this).gsClass = null,
        this.func = u;
        var h = [];
        this.check = function(t) {
            for (var e, i, n, r, o = l.length, s = o; -1 < --o; )
                (e = x[l[o]] || new T(l[o],[])).gsClass ? (h[o] = e.gsClass,
                s--) : t && e.sc.push(this);
            if (0 === s && u) {
                if (n = (i = ("com.greensock." + a).split(".")).pop(),
                r = v(i.join("."))[n] = this.gsClass = u.apply(u, h),
                c)
                    if (g[n] = p[n] = r,
                    "undefined" != typeof module && module.exports)
                        if (a === d)
                            for (o in module.exports = p[d] = r,
                            p)
                                r[o] = p[o];
                        else
                            p[d] && (p[d][n] = r);
                    else
                        "function" == typeof define && define.amd && define((f.GreenSockAMDPath ? f.GreenSockAMDPath + "/" : "") + a.split(".").pop(), [], function() {
                            return r
                        });
                for (o = 0; o < this.sc.length; o++)
                    this.sc[o].check()
            }
        }
        ,
        this.check(!0)
    }, a = f._gsDefine = function(t, e, i, n) {
        return new T(t,e,i,n)
    }
    , C = h._class = function(t, e, i) {
        return e = e || function() {}
        ,
        a(t, [], function() {
            return e
        }, i),
        e
    }
    ;
    a.globals = g;
    var u = [0, 0, 1, 1]
      , k = C("easing.Ease", function(t, e, i, n) {
        this._func = t,
        this._type = i || 0,
        this._power = n || 0,
        this._params = e ? u.concat(e) : u
    }, !0)
      , S = k.map = {}
      , c = k.register = function(t, e, i, n) {
        for (var r, o, s, a, l = e.split(","), u = l.length, c = (i || "easeIn,easeOut,easeInOut").split(","); -1 < --u; )
            for (o = l[u],
            r = n ? C("easing." + o, null, !0) : h.easing[o] || {},
            s = c.length; -1 < --s; )
                a = c[s],
                S[o + "." + a] = S[a + o] = r[a] = t.getRatio ? t : t[a] || new t
    }
    ;
    for ((r = k.prototype)._calcEnd = !1,
    r.getRatio = function(t) {
        if (this._func)
            return this._params[0] = t,
            this._func.apply(null, this._params);
        var e = this._type
          , i = this._power
          , n = 1 === e ? 1 - t : 2 === e ? t : t < .5 ? 2 * t : 2 * (1 - t);
        return 1 === i ? n *= n : 2 === i ? n *= n * n : 3 === i ? n *= n * n * n : 4 === i && (n *= n * n * n * n),
        1 === e ? 1 - n : 2 === e ? n : t < .5 ? n / 2 : 1 - n / 2
    }
    ,
    i = (e = ["Linear", "Quad", "Cubic", "Quart", "Quint,Strong"]).length; -1 < --i; )
        r = e[i] + ",Power" + i,
        c(new k(null,null,1,i), r, "easeOut", !0),
        c(new k(null,null,2,i), r, "easeIn" + (0 === i ? ",easeNone" : "")),
        c(new k(null,null,3,i), r, "easeInOut");
    S.linear = h.easing.Linear.easeIn,
    S.swing = h.easing.Quad.easeInOut;
    var $ = C("events.EventDispatcher", function(t) {
        this._listeners = {},
        this._eventTarget = t || this
    });
    (r = $.prototype).addEventListener = function(t, e, i, n, r) {
        r = r || 0;
        var o, s, a = this._listeners[t], l = 0;
        for (this !== _ || m || _.wake(),
        null == a && (this._listeners[t] = a = []),
        s = a.length; -1 < --s; )
            (o = a[s]).c === e && o.s === i ? a.splice(s, 1) : 0 === l && o.pr < r && (l = s + 1);
        a.splice(l, 0, {
            c: e,
            s: i,
            up: n,
            pr: r
        })
    }
    ,
    r.removeEventListener = function(t, e) {
        var i, n = this._listeners[t];
        if (n)
            for (i = n.length; -1 < --i; )
                if (n[i].c === e)
                    return void n.splice(i, 1)
    }
    ,
    r.dispatchEvent = function(t) {
        var e, i, n, r = this._listeners[t];
        if (r)
            for (1 < (e = r.length) && (r = r.slice(0)),
            i = this._eventTarget; -1 < --e; )
                (n = r[e]) && (n.up ? n.c.call(n.s || i, {
                    type: t,
                    target: i
                }) : n.c.call(n.s || i))
    }
    ;
    var D = f.requestAnimationFrame
      , A = f.cancelAnimationFrame
      , E = Date.now || function() {
        return (new Date).getTime()
    }
      , P = E();
    for (i = (e = ["ms", "moz", "webkit", "o"]).length; -1 < --i && !D; )
        D = f[e[i] + "RequestAnimationFrame"],
        A = f[e[i] + "CancelAnimationFrame"] || f[e[i] + "CancelRequestAnimationFrame"];
    C("Ticker", function(t, e) {
        var r, o, s, a, l, u = this, c = E(), i = !(!1 === e || !D) && "auto", h = 500, f = 33, d = function(t) {
            var e, i, n = E() - P;
            h < n && (c += n - f),
            P += n,
            u.time = (P - c) / 1e3,
            e = u.time - l,
            (!r || 0 < e || !0 === t) && (u.frame++,
            l += e + (a <= e ? .004 : a - e),
            i = !0),
            !0 !== t && (s = o(d)),
            i && u.dispatchEvent("tick")
        };
        $.call(u),
        u.time = u.frame = 0,
        u.tick = function() {
            d(!0)
        }
        ,
        u.lagSmoothing = function(t, e) {
            return arguments.length ? (h = t || 1e10,
            void (f = Math.min(e, h, 0))) : h < 1e10
        }
        ,
        u.sleep = function() {
            null != s && (i && A ? A(s) : clearTimeout(s),
            o = w,
            s = null,
            u === _ && (m = !1))
        }
        ,
        u.wake = function(t) {
            null !== s ? u.sleep() : t ? c += -P + (P = E()) : 10 < u.frame && (P = E() - h + 5),
            o = 0 === r ? w : i && D ? D : function(t) {
                return setTimeout(t, 1e3 * (l - u.time) + 1 | 0)
            }
            ,
            u === _ && (m = !0),
            d(2)
        }
        ,
        u.fps = function(t) {
            return arguments.length ? (a = 1 / ((r = t) || 60),
            l = this.time + a,
            void u.wake()) : r
        }
        ,
        u.useRAF = function(t) {
            return arguments.length ? (u.sleep(),
            i = t,
            void u.fps(r)) : i
        }
        ,
        u.fps(t),
        setTimeout(function() {
            "auto" === i && u.frame < 5 && "hidden" !== (n || {}).visibilityState && u.useRAF(!1)
        }, 1500)
    }),
    (r = h.Ticker.prototype = new h.events.EventDispatcher).constructor = h.Ticker;
    var O = C("core.Animation", function(t, e) {
        if (this.vars = e = e || {},
        this._duration = this._totalDuration = t || 0,
        this._delay = Number(e.delay) || 0,
        this._timeScale = 1,
        this._active = !0 === e.immediateRender,
        this.data = e.data,
        this._reversed = !0 === e.reversed,
        Q) {
            m || _.wake();
            var i = this.vars.useFrames ? Z : Q;
            i.add(this, i._time),
            this.vars.paused && this.paused(!0)
        }
    });
    _ = O.ticker = new h.Ticker,
    (r = O.prototype)._dirty = r._gc = r._initted = r._paused = !1,
    r._totalTime = r._time = 0,
    r._rawPrevTime = -1,
    r._next = r._last = r._onUpdate = r._timeline = r.timeline = null,
    r._paused = !1;
    var R = function() {
        m && 2e3 < E() - P && ("hidden" !== (n || {}).visibilityState || !_.lagSmoothing()) && _.wake();
        var t = setTimeout(R, 2e3);
        t.unref && t.unref()
    };
    R(),
    r.play = function(t, e) {
        return null != t && this.seek(t, e),
        this.reversed(!1).paused(!1)
    }
    ,
    r.pause = function(t, e) {
        return null != t && this.seek(t, e),
        this.paused(!0)
    }
    ,
    r.resume = function(t, e) {
        return null != t && this.seek(t, e),
        this.paused(!1)
    }
    ,
    r.seek = function(t, e) {
        return this.totalTime(Number(t), !1 !== e)
    }
    ,
    r.restart = function(t, e) {
        return this.reversed(!1).paused(!1).totalTime(t ? -this._delay : 0, !1 !== e, !0)
    }
    ,
    r.reverse = function(t, e) {
        return null != t && this.seek(t || this.totalDuration(), e),
        this.reversed(!0).paused(!1)
    }
    ,
    r.render = function(t, e, i) {}
    ,
    r.invalidate = function() {
        return this._time = this._totalTime = 0,
        this._initted = this._gc = !1,
        this._rawPrevTime = -1,
        (this._gc || !this.timeline) && this._enabled(!0),
        this
    }
    ,
    r.isActive = function() {
        var t, e = this._timeline, i = this._startTime;
        return !e || !this._gc && !this._paused && e.isActive() && (t = e.rawTime(!0)) >= i && t < i + this.totalDuration() / this._timeScale - 1e-7
    }
    ,
    r._enabled = function(t, e) {
        return m || _.wake(),
        this._gc = !t,
        this._active = this.isActive(),
        !0 !== e && (t && !this.timeline ? this._timeline.add(this, this._startTime - this._delay) : !t && this.timeline && this._timeline._remove(this, !0)),
        !1
    }
    ,
    r._kill = function(t, e) {
        return this._enabled(!1, !1)
    }
    ,
    r.kill = function(t, e) {
        return this._kill(t, e),
        this
    }
    ,
    r._uncache = function(t) {
        for (var e = t ? this : this.timeline; e; )
            e._dirty = !0,
            e = e.timeline;
        return this
    }
    ,
    r._swapSelfInParams = function(t) {
        for (var e = t.length, i = t.concat(); -1 < --e; )
            "{self}" === t[e] && (i[e] = this);
        return i
    }
    ,
    r._callback = function(t) {
        var e = this.vars
          , i = e[t]
          , n = e[t + "Params"]
          , r = e[t + "Scope"] || e.callbackScope || this;
        switch (n ? n.length : 0) {
        case 0:
            i.call(r);
            break;
        case 1:
            i.call(r, n[0]);
            break;
        case 2:
            i.call(r, n[0], n[1]);
            break;
        default:
            i.apply(r, n)
        }
    }
    ,
    r.eventCallback = function(t, e, i, n) {
        if ("on" === (t || "").substr(0, 2)) {
            var r = this.vars;
            if (1 === arguments.length)
                return r[t];
            null == e ? delete r[t] : (r[t] = e,
            r[t + "Params"] = b(i) && -1 !== i.join("").indexOf("{self}") ? this._swapSelfInParams(i) : i,
            r[t + "Scope"] = n),
            "onUpdate" === t && (this._onUpdate = e)
        }
        return this
    }
    ,
    r.delay = function(t) {
        return arguments.length ? (this._timeline.smoothChildTiming && this.startTime(this._startTime + t - this._delay),
        this._delay = t,
        this) : this._delay
    }
    ,
    r.duration = function(t) {
        return arguments.length ? (this._duration = this._totalDuration = t,
        this._uncache(!0),
        this._timeline.smoothChildTiming && 0 < this._time && this._time < this._duration && 0 !== t && this.totalTime(this._totalTime * (t / this._duration), !0),
        this) : (this._dirty = !1,
        this._duration)
    }
    ,
    r.totalDuration = function(t) {
        return this._dirty = !1,
        arguments.length ? this.duration(t) : this._totalDuration
    }
    ,
    r.time = function(t, e) {
        return arguments.length ? (this._dirty && this.totalDuration(),
        this.totalTime(t > this._duration ? this._duration : t, e)) : this._time
    }
    ,
    r.totalTime = function(t, e, i) {
        if (m || _.wake(),
        !arguments.length)
            return this._totalTime;
        if (this._timeline) {
            if (t < 0 && !i && (t += this.totalDuration()),
            this._timeline.smoothChildTiming) {
                this._dirty && this.totalDuration();
                var n = this._totalDuration
                  , r = this._timeline;
                if (n < t && !i && (t = n),
                this._startTime = (this._paused ? this._pauseTime : r._time) - (this._reversed ? n - t : t) / this._timeScale,
                r._dirty || this._uncache(!1),
                r._timeline)
                    for (; r._timeline; )
                        r._timeline._time !== (r._startTime + r._totalTime) / r._timeScale && r.totalTime(r._totalTime, !0),
                        r = r._timeline
            }
            this._gc && this._enabled(!0, !1),
            (this._totalTime !== t || 0 === this._duration) && (F.length && J(),
            this.render(t, e, !1),
            F.length && J())
        }
        return this
    }
    ,
    r.progress = r.totalProgress = function(t, e) {
        var i = this.duration();
        return arguments.length ? this.totalTime(i * t, e) : i ? this._time / i : this.ratio
    }
    ,
    r.startTime = function(t) {
        return arguments.length ? (t !== this._startTime && (this._startTime = t,
        this.timeline && this.timeline._sortChildren && this.timeline.add(this, t - this._delay)),
        this) : this._startTime
    }
    ,
    r.endTime = function(t) {
        return this._startTime + (0 != t ? this.totalDuration() : this.duration()) / this._timeScale
    }
    ,
    r.timeScale = function(t) {
        if (!arguments.length)
            return this._timeScale;
        var e, i;
        for (t = t || y,
        this._timeline && this._timeline.smoothChildTiming && (i = (e = this._pauseTime) || 0 === e ? e : this._timeline.totalTime(),
        this._startTime = i - (i - this._startTime) * this._timeScale / t),
        this._timeScale = t,
        i = this.timeline; i && i.timeline; )
            i._dirty = !0,
            i.totalDuration(),
            i = i.timeline;
        return this
    }
    ,
    r.reversed = function(t) {
        return arguments.length ? (t != this._reversed && (this._reversed = t,
        this.totalTime(this._timeline && !this._timeline.smoothChildTiming ? this.totalDuration() - this._totalTime : this._totalTime, !0)),
        this) : this._reversed
    }
    ,
    r.paused = function(t) {
        if (!arguments.length)
            return this._paused;
        var e, i, n = this._timeline;
        return t != this._paused && n && (m || t || _.wake(),
        i = (e = n.rawTime()) - this._pauseTime,
        !t && n.smoothChildTiming && (this._startTime += i,
        this._uncache(!1)),
        this._pauseTime = t ? e : null,
        this._paused = t,
        this._active = this.isActive(),
        !t && 0 !== i && this._initted && this.duration() && (e = n.smoothChildTiming ? this._totalTime : (e - this._startTime) / this._timeScale,
        this.render(e, e === this._totalTime, !0))),
        this._gc && !t && this._enabled(!0, !1),
        this
    }
    ;
    var M = C("core.SimpleTimeline", function(t) {
        O.call(this, 0, t),
        this.autoRemoveChildren = this.smoothChildTiming = !0
    });
    (r = M.prototype = new O).constructor = M,
    r.kill()._gc = !1,
    r._first = r._last = r._recent = null,
    r._sortChildren = !1,
    r.add = r.insert = function(t, e, i, n) {
        var r, o;
        if (t._startTime = Number(e || 0) + t._delay,
        t._paused && this !== t._timeline && (t._pauseTime = this.rawTime() - (t._timeline.rawTime() - t._pauseTime)),
        t.timeline && t.timeline._remove(t, !0),
        t.timeline = t._timeline = this,
        t._gc && t._enabled(!0, !0),
        r = this._last,
        this._sortChildren)
            for (o = t._startTime; r && r._startTime > o; )
                r = r._prev;
        return r ? (t._next = r._next,
        r._next = t) : (t._next = this._first,
        this._first = t),
        t._next ? t._next._prev = t : this._last = t,
        t._prev = r,
        this._recent = t,
        this._timeline && this._uncache(!0),
        this
    }
    ,
    r._remove = function(t, e) {
        return t.timeline === this && (e || t._enabled(!1, !0),
        t._prev ? t._prev._next = t._next : this._first === t && (this._first = t._next),
        t._next ? t._next._prev = t._prev : this._last === t && (this._last = t._prev),
        t._next = t._prev = t.timeline = null,
        t === this._recent && (this._recent = this._last),
        this._timeline && this._uncache(!0)),
        this
    }
    ,
    r.render = function(t, e, i) {
        var n, r = this._first;
        for (this._totalTime = this._time = this._rawPrevTime = t; r; )
            n = r._next,
            (r._active || t >= r._startTime && !r._paused && !r._gc) && (r._reversed ? r.render((r._dirty ? r.totalDuration() : r._totalDuration) - (t - r._startTime) * r._timeScale, e, i) : r.render((t - r._startTime) * r._timeScale, e, i)),
            r = n
    }
    ,
    r.rawTime = function() {
        return m || _.wake(),
        this._totalTime
    }
    ;
    var j = C("TweenLite", function(t, e, i) {
        if (O.call(this, e, i),
        this.render = j.prototype.render,
        null == t)
            throw "Cannot tween a null target.";
        this.target = t = "string" != typeof t ? t : j.selector(t) || t;
        var n, r, o, s = t.jquery || t.length && t !== f && t[0] && (t[0] === f || t[0].nodeType && t[0].style && !t.nodeType), a = this.vars.overwrite;
        if (this._overwrite = a = null == a ? Y[j.defaultOverwrite] : "number" == typeof a ? a >> 0 : Y[a],
        (s || t instanceof Array || t.push && b(t)) && "number" != typeof t[0])
            for (this._targets = o = l(t),
            this._propLookup = [],
            this._siblings = [],
            n = 0; n < o.length; n++)
                (r = o[n]) ? "string" != typeof r ? r.length && r !== f && r[0] && (r[0] === f || r[0].nodeType && r[0].style && !r.nodeType) ? (o.splice(n--, 1),
                this._targets = o = o.concat(l(r))) : (this._siblings[n] = tt(r, this, !1),
                1 === a && 1 < this._siblings[n].length && it(r, this, null, 1, this._siblings[n])) : "string" == typeof (r = o[n--] = j.selector(r)) && o.splice(n + 1, 1) : o.splice(n--, 1);
        else
            this._propLookup = {},
            this._siblings = tt(t, this, !1),
            1 === a && 1 < this._siblings.length && it(t, this, null, 1, this._siblings);
        (this.vars.immediateRender || 0 === e && 0 === this._delay && !1 !== this.vars.immediateRender) && (this._time = -y,
        this.render(Math.min(0, -this._delay)))
    }, !0)
      , z = function(t) {
        return t && t.length && t !== f && t[0] && (t[0] === f || t[0].nodeType && t[0].style && !t.nodeType)
    };
    (r = j.prototype = new O).constructor = j,
    r.kill()._gc = !1,
    r.ratio = 0,
    r._firstPT = r._targets = r._overwrittenProps = r._startAt = null,
    r._notifyPluginsOfEnabled = r._lazy = !1,
    j.version = "2.0.2",
    j.defaultEase = r._ease = new k(null,null,1,1),
    j.defaultOverwrite = "auto",
    j.ticker = _,
    j.autoSleep = 120,
    j.lagSmoothing = function(t, e) {
        _.lagSmoothing(t, e)
    }
    ,
    j.selector = f.$ || f.jQuery || function(t) {
        var e = f.$ || f.jQuery;
        return e ? (j.selector = e)(t) : (n || (n = f.document),
        n ? n.querySelectorAll ? n.querySelectorAll(t) : n.getElementById("#" === t.charAt(0) ? t.substr(1) : t) : t)
    }
    ;
    var F = []
      , I = {}
      , N = /(?:(-|-=|\+=)?\d*\.?\d*(?:e[\-+]?\d+)?)[0-9]/gi
      , B = /[\+-]=-?[\.\d]/
      , L = function(t) {
        for (var e, i = this._firstPT; i; )
            e = i.blob ? 1 === t && null != this.end ? this.end : t ? this.join("") : this.start : i.c * t + i.s,
            i.m ? e = i.m.call(this._tween, e, this._target || i.t, this._tween) : e < 1e-6 && -1e-6 < e && !i.blob && (e = 0),
            i.f ? i.fp ? i.t[i.p](i.fp, e) : i.t[i.p](e) : i.t[i.p] = e,
            i = i._next
    }
      , q = function(t, e, i, n) {
        var r, o, s, a, l, u, c, h = [], f = 0, d = "", p = 0;
        for (h.start = t,
        h.end = e,
        t = h[0] = t + "",
        e = h[1] = e + "",
        i && (i(h),
        t = h[0],
        e = h[1]),
        h.length = 0,
        r = t.match(N) || [],
        o = e.match(N) || [],
        n && (n._next = null,
        n.blob = 1,
        h._firstPT = h._applyPT = n),
        l = o.length,
        a = 0; a < l; a++)
            c = o[a],
            d += (u = e.substr(f, e.indexOf(c, f) - f)) || !a ? u : ",",
            f += u.length,
            p ? p = (p + 1) % 5 : "rgba(" === u.substr(-5) && (p = 1),
            c === r[a] || r.length <= a ? d += c : (d && (h.push(d),
            d = ""),
            s = parseFloat(r[a]),
            h.push(s),
            h._firstPT = {
                _next: h._firstPT,
                t: h,
                p: h.length - 1,
                s: s,
                c: ("=" === c.charAt(1) ? parseInt(c.charAt(0) + "1", 10) * parseFloat(c.substr(2)) : parseFloat(c) - s) || 0,
                f: 0,
                m: p && p < 4 ? Math.round : 0
            }),
            f += c.length;
        return (d += e.substr(f)) && h.push(d),
        h.setRatio = L,
        B.test(e) && (h.end = null),
        h
    }
      , W = function(t, e, i, n, r, o, s, a, l) {
        "function" == typeof n && (n = n(l || 0, t));
        var u = typeof t[e]
          , c = "function" !== u ? "" : e.indexOf("set") || "function" != typeof t["get" + e.substr(3)] ? e : "get" + e.substr(3)
          , h = "get" !== i ? i : c ? s ? t[c](s) : t[c]() : t[e]
          , f = "string" == typeof n && "=" === n.charAt(1)
          , d = {
            t: t,
            p: e,
            s: h,
            f: "function" === u,
            pg: 0,
            n: r || e,
            m: o ? "function" == typeof o ? o : Math.round : 0,
            pr: 0,
            c: f ? parseInt(n.charAt(0) + "1", 10) * parseFloat(n.substr(2)) : parseFloat(n) - h || 0
        };
        return ("number" != typeof h || "number" != typeof n && !f) && (s || isNaN(h) || !f && isNaN(n) || "boolean" == typeof h || "boolean" == typeof n ? (d.fp = s,
        d = {
            t: q(h, f ? parseFloat(d.s) + d.c + (d.s + "").replace(/[0-9\-\.]/g, "") : n, a || j.defaultStringFilter, d),
            p: "setRatio",
            s: 0,
            c: 1,
            f: 2,
            pg: 0,
            n: r || e,
            pr: 0,
            m: 0
        }) : (d.s = parseFloat(h),
        f || (d.c = parseFloat(n) - d.s || 0))),
        d.c ? ((d._next = this._firstPT) && (d._next._prev = d),
        this._firstPT = d) : void 0
    }
      , H = j._internals = {
        isArray: b,
        isSelector: z,
        lazyTweens: F,
        blobDif: q
    }
      , U = j._plugins = {}
      , V = H.tweenLookup = {}
      , X = 0
      , G = H.reservedProps = {
        ease: 1,
        delay: 1,
        overwrite: 1,
        onComplete: 1,
        onCompleteParams: 1,
        onCompleteScope: 1,
        useFrames: 1,
        runBackwards: 1,
        startAt: 1,
        onUpdate: 1,
        onUpdateParams: 1,
        onUpdateScope: 1,
        onStart: 1,
        onStartParams: 1,
        onStartScope: 1,
        onReverseComplete: 1,
        onReverseCompleteParams: 1,
        onReverseCompleteScope: 1,
        onRepeat: 1,
        onRepeatParams: 1,
        onRepeatScope: 1,
        easeParams: 1,
        yoyo: 1,
        immediateRender: 1,
        repeat: 1,
        repeatDelay: 1,
        data: 1,
        paused: 1,
        reversed: 1,
        autoCSS: 1,
        lazy: 1,
        onOverwrite: 1,
        callbackScope: 1,
        stringFilter: 1,
        id: 1,
        yoyoEase: 1
    }
      , Y = {
        none: 0,
        all: 1,
        auto: 2,
        concurrent: 3,
        allOnStart: 4,
        preexisting: 5,
        true: 1,
        false: 0
    }
      , Z = O._rootFramesTimeline = new M
      , Q = O._rootTimeline = new M
      , K = 30
      , J = H.lazyRender = function() {
        var t, e = F.length;
        for (I = {}; -1 < --e; )
            (t = F[e]) && !1 !== t._lazy && (t.render(t._lazy[0], t._lazy[1], !0),
            t._lazy = !1);
        F.length = 0
    }
    ;
    Q._startTime = _.time,
    Z._startTime = _.frame,
    Q._active = Z._active = !0,
    setTimeout(J, 1),
    O._updateRoot = j.render = function() {
        var t, e, i;
        if (F.length && J(),
        Q.render((_.time - Q._startTime) * Q._timeScale, !1, !1),
        Z.render((_.frame - Z._startTime) * Z._timeScale, !1, !1),
        F.length && J(),
        _.frame >= K) {
            for (i in K = _.frame + (parseInt(j.autoSleep, 10) || 120),
            V) {
                for (t = (e = V[i].tweens).length; -1 < --t; )
                    e[t]._gc && e.splice(t, 1);
                0 === e.length && delete V[i]
            }
            if ((!(i = Q._first) || i._paused) && j.autoSleep && !Z._first && 1 === _._listeners.tick.length) {
                for (; i && i._paused; )
                    i = i._next;
                i || _.sleep()
            }
        }
    }
    ,
    _.addEventListener("tick", O._updateRoot);
    var tt = function(t, e, i) {
        var n, r, o = t._gsTweenID;
        if (V[o || (t._gsTweenID = o = "t" + X++)] || (V[o] = {
            target: t,
            tweens: []
        }),
        e && ((n = V[o].tweens)[r = n.length] = e,
        i))
            for (; -1 < --r; )
                n[r] === e && n.splice(r, 1);
        return V[o].tweens
    }
      , et = function(t, e, i, n) {
        var r, o, s = t.vars.onOverwrite;
        return s && (r = s(t, e, i, n)),
        (s = j.onOverwrite) && (o = s(t, e, i, n)),
        !1 !== r && !1 !== o
    }
      , it = function(t, e, i, n, r) {
        var o, s, a, l;
        if (1 === n || 4 <= n) {
            for (l = r.length,
            o = 0; o < l; o++)
                if ((a = r[o]) !== e)
                    a._gc || a._kill(null, t, e) && (s = !0);
                else if (5 === n)
                    break;
            return s
        }
        var u, c = e._startTime + y, h = [], f = 0, d = 0 === e._duration;
        for (o = r.length; -1 < --o; )
            (a = r[o]) === e || a._gc || a._paused || (a._timeline !== e._timeline ? (u = u || nt(e, 0, d),
            0 === nt(a, u, d) && (h[f++] = a)) : a._startTime <= c && a._startTime + a.totalDuration() / a._timeScale > c && ((d || !a._initted) && c - a._startTime <= 2e-10 || (h[f++] = a)));
        for (o = f; -1 < --o; )
            if (l = (a = h[o])._firstPT,
            2 === n && a._kill(i, t, e) && (s = !0),
            2 !== n || !a._firstPT && a._initted && l) {
                if (2 !== n && !et(a, e))
                    continue;
                a._enabled(!1, !1) && (s = !0)
            }
        return s
    }
      , nt = function(t, e, i) {
        for (var n = t._timeline, r = n._timeScale, o = t._startTime; n._timeline; ) {
            if (o += n._startTime,
            r *= n._timeScale,
            n._paused)
                return -100;
            n = n._timeline
        }
        return e < (o /= r) ? o - e : i && o === e || !t._initted && o - e < 2e-10 ? y : (o += t.totalDuration() / t._timeScale / r) > e + y ? 0 : o - e - y
    };
    r._init = function() {
        var t, e, i, n, r, o, s = this.vars, a = this._overwrittenProps, l = this._duration, u = !!s.immediateRender, c = s.ease;
        if (s.startAt) {
            for (n in this._startAt && (this._startAt.render(-1, !0),
            this._startAt.kill()),
            r = {},
            s.startAt)
                r[n] = s.startAt[n];
            if (r.data = "isStart",
            r.overwrite = !1,
            r.immediateRender = !0,
            r.lazy = u && !1 !== s.lazy,
            r.startAt = r.delay = null,
            r.onUpdate = s.onUpdate,
            r.onUpdateParams = s.onUpdateParams,
            r.onUpdateScope = s.onUpdateScope || s.callbackScope || this,
            this._startAt = j.to(this.target || {}, 0, r),
            u)
                if (0 < this._time)
                    this._startAt = null;
                else if (0 !== l)
                    return
        } else if (s.runBackwards && 0 !== l)
            if (this._startAt)
                this._startAt.render(-1, !0),
                this._startAt.kill(),
                this._startAt = null;
            else {
                for (n in 0 !== this._time && (u = !1),
                i = {},
                s)
                    G[n] && "autoCSS" !== n || (i[n] = s[n]);
                if (i.overwrite = 0,
                i.data = "isFromStart",
                i.lazy = u && !1 !== s.lazy,
                i.immediateRender = u,
                this._startAt = j.to(this.target, 0, i),
                u) {
                    if (0 === this._time)
                        return
                } else
                    this._startAt._init(),
                    this._startAt._enabled(!1),
                    this.vars.immediateRender && (this._startAt = null)
            }
        if (this._ease = c = c ? c instanceof k ? c : "function" == typeof c ? new k(c,s.easeParams) : S[c] || j.defaultEase : j.defaultEase,
        s.easeParams instanceof Array && c.config && (this._ease = c.config.apply(c, s.easeParams)),
        this._easeType = this._ease._type,
        this._easePower = this._ease._power,
        this._firstPT = null,
        this._targets)
            for (o = this._targets.length,
            t = 0; t < o; t++)
                this._initProps(this._targets[t], this._propLookup[t] = {}, this._siblings[t], a ? a[t] : null, t) && (e = !0);
        else
            e = this._initProps(this.target, this._propLookup, this._siblings, a, 0);
        if (e && j._onPluginEvent("_onInitAllProps", this),
        a && (this._firstPT || "function" != typeof this.target && this._enabled(!1, !1)),
        s.runBackwards)
            for (i = this._firstPT; i; )
                i.s += i.c,
                i.c = -i.c,
                i = i._next;
        this._onUpdate = s.onUpdate,
        this._initted = !0
    }
    ,
    r._initProps = function(t, e, i, n, r) {
        var o, s, a, l, u, c;
        if (null == t)
            return !1;
        for (o in I[t._gsTweenID] && J(),
        this.vars.css || t.style && t !== f && t.nodeType && U.css && !1 !== this.vars.autoCSS && function(t, e) {
            var i, n = {};
            for (i in t)
                G[i] || i in e && "transform" !== i && "x" !== i && "y" !== i && "width" !== i && "height" !== i && "className" !== i && "border" !== i || !(!U[i] || U[i] && U[i]._autoCSS) || (n[i] = t[i],
                delete t[i]);
            t.css = n
        }(this.vars, t),
        this.vars)
            if (c = this.vars[o],
            G[o])
                c && (c instanceof Array || c.push && b(c)) && -1 !== c.join("").indexOf("{self}") && (this.vars[o] = c = this._swapSelfInParams(c, this));
            else if (U[o] && (l = new U[o])._onInitTween(t, this.vars[o], this, r)) {
                for (this._firstPT = u = {
                    _next: this._firstPT,
                    t: l,
                    p: "setRatio",
                    s: 0,
                    c: 1,
                    f: 1,
                    n: o,
                    pg: 1,
                    pr: l._priority,
                    m: 0
                },
                s = l._overwriteProps.length; -1 < --s; )
                    e[l._overwriteProps[s]] = this._firstPT;
                (l._priority || l._onInitAllProps) && (a = !0),
                (l._onDisable || l._onEnable) && (this._notifyPluginsOfEnabled = !0),
                u._next && (u._next._prev = u)
            } else
                e[o] = W.call(this, t, o, "get", c, o, 0, null, this.vars.stringFilter, r);
        return n && this._kill(n, t) ? this._initProps(t, e, i, n, r) : 1 < this._overwrite && this._firstPT && 1 < i.length && it(t, this, e, this._overwrite, i) ? (this._kill(e, t),
        this._initProps(t, e, i, n, r)) : (this._firstPT && (!1 !== this.vars.lazy && this._duration || this.vars.lazy && !this._duration) && (I[t._gsTweenID] = !0),
        a)
    }
    ,
    r.render = function(t, e, i) {
        var n, r, o, s, a = this._time, l = this._duration, u = this._rawPrevTime;
        if (l - 1e-7 <= t && 0 <= t)
            this._totalTime = this._time = l,
            this.ratio = this._ease._calcEnd ? this._ease.getRatio(1) : 1,
            this._reversed || (n = !0,
            r = "onComplete",
            i = i || this._timeline.autoRemoveChildren),
            0 === l && (this._initted || !this.vars.lazy || i) && (this._startTime === this._timeline._duration && (t = 0),
            (u < 0 || t <= 0 && -1e-7 <= t || u === y && "isPause" !== this.data) && u !== t && (i = !0,
            y < u && (r = "onReverseComplete")),
            this._rawPrevTime = s = !e || t || u === t ? t : y);
        else if (t < 1e-7)
            this._totalTime = this._time = 0,
            this.ratio = this._ease._calcEnd ? this._ease.getRatio(0) : 0,
            (0 !== a || 0 === l && 0 < u) && (r = "onReverseComplete",
            n = this._reversed),
            t < 0 && (this._active = !1,
            0 === l && (this._initted || !this.vars.lazy || i) && (0 <= u && (u !== y || "isPause" !== this.data) && (i = !0),
            this._rawPrevTime = s = !e || t || u === t ? t : y)),
            (!this._initted || this._startAt && this._startAt.progress()) && (i = !0);
        else if (this._totalTime = this._time = t,
        this._easeType) {
            var c = t / l
              , h = this._easeType
              , f = this._easePower;
            (1 === h || 3 === h && .5 <= c) && (c = 1 - c),
            3 === h && (c *= 2),
            1 === f ? c *= c : 2 === f ? c *= c * c : 3 === f ? c *= c * c * c : 4 === f && (c *= c * c * c * c),
            this.ratio = 1 === h ? 1 - c : 2 === h ? c : t / l < .5 ? c / 2 : 1 - c / 2
        } else
            this.ratio = this._ease.getRatio(t / l);
        if (this._time !== a || i) {
            if (!this._initted) {
                if (this._init(),
                !this._initted || this._gc)
                    return;
                if (!i && this._firstPT && (!1 !== this.vars.lazy && this._duration || this.vars.lazy && !this._duration))
                    return this._time = this._totalTime = a,
                    this._rawPrevTime = u,
                    F.push(this),
                    void (this._lazy = [t, e]);
                this._time && !n ? this.ratio = this._ease.getRatio(this._time / l) : n && this._ease._calcEnd && (this.ratio = this._ease.getRatio(0 === this._time ? 0 : 1))
            }
            for (!1 !== this._lazy && (this._lazy = !1),
            this._active || !this._paused && this._time !== a && 0 <= t && (this._active = !0),
            0 === a && (this._startAt && (0 <= t ? this._startAt.render(t, !0, i) : r || (r = "_dummyGS")),
            this.vars.onStart && (0 !== this._time || 0 === l) && (e || this._callback("onStart"))),
            o = this._firstPT; o; )
                o.f ? o.t[o.p](o.c * this.ratio + o.s) : o.t[o.p] = o.c * this.ratio + o.s,
                o = o._next;
            this._onUpdate && (t < 0 && this._startAt && -1e-4 !== t && this._startAt.render(t, !0, i),
            e || (this._time !== a || n || i) && this._callback("onUpdate")),
            r && (!this._gc || i) && (t < 0 && this._startAt && !this._onUpdate && -1e-4 !== t && this._startAt.render(t, !0, i),
            n && (this._timeline.autoRemoveChildren && this._enabled(!1, !1),
            this._active = !1),
            !e && this.vars[r] && this._callback(r),
            0 === l && this._rawPrevTime === y && s !== y && (this._rawPrevTime = 0))
        }
    }
    ,
    r._kill = function(t, e, i) {
        if ("all" === t && (t = null),
        null == t && (null == e || e === this.target))
            return this._lazy = !1,
            this._enabled(!1, !1);
        e = "string" != typeof e ? e || this._targets || this.target : j.selector(e) || e;
        var n, r, o, s, a, l, u, c, h, f = i && this._time && i._startTime === this._startTime && this._timeline === i._timeline, d = this._firstPT;
        if ((b(e) || z(e)) && "number" != typeof e[0])
            for (n = e.length; -1 < --n; )
                this._kill(t, e[n], i) && (l = !0);
        else {
            if (this._targets) {
                for (n = this._targets.length; -1 < --n; )
                    if (e === this._targets[n]) {
                        a = this._propLookup[n] || {},
                        this._overwrittenProps = this._overwrittenProps || [],
                        r = this._overwrittenProps[n] = t ? this._overwrittenProps[n] || {} : "all";
                        break
                    }
            } else {
                if (e !== this.target)
                    return !1;
                a = this._propLookup,
                r = this._overwrittenProps = t ? this._overwrittenProps || {} : "all"
            }
            if (a) {
                if (u = t || a,
                c = t !== r && "all" !== r && t !== a && ("object" != typeof t || !t._tempKill),
                i && (j.onOverwrite || this.vars.onOverwrite)) {
                    for (o in u)
                        a[o] && (h || (h = []),
                        h.push(o));
                    if ((h || !t) && !et(this, i, e, h))
                        return !1
                }
                for (o in u)
                    (s = a[o]) && (f && (s.f ? s.t[s.p](s.s) : s.t[s.p] = s.s,
                    l = !0),
                    s.pg && s.t._kill(u) && (l = !0),
                    s.pg && 0 !== s.t._overwriteProps.length || (s._prev ? s._prev._next = s._next : s === this._firstPT && (this._firstPT = s._next),
                    s._next && (s._next._prev = s._prev),
                    s._next = s._prev = null),
                    delete a[o]),
                    c && (r[o] = 1);
                !this._firstPT && this._initted && d && this._enabled(!1, !1)
            }
        }
        return l
    }
    ,
    r.invalidate = function() {
        return this._notifyPluginsOfEnabled && j._onPluginEvent("_onDisable", this),
        this._firstPT = this._overwrittenProps = this._startAt = this._onUpdate = null,
        this._notifyPluginsOfEnabled = this._active = this._lazy = !1,
        this._propLookup = this._targets ? {} : [],
        O.prototype.invalidate.call(this),
        this.vars.immediateRender && (this._time = -y,
        this.render(Math.min(0, -this._delay))),
        this
    }
    ,
    r._enabled = function(t, e) {
        if (m || _.wake(),
        t && this._gc) {
            var i, n = this._targets;
            if (n)
                for (i = n.length; -1 < --i; )
                    this._siblings[i] = tt(n[i], this, !0);
            else
                this._siblings = tt(this.target, this, !0)
        }
        return O.prototype._enabled.call(this, t, e),
        !(!this._notifyPluginsOfEnabled || !this._firstPT) && j._onPluginEvent(t ? "_onEnable" : "_onDisable", this)
    }
    ,
    j.to = function(t, e, i) {
        return new j(t,e,i)
    }
    ,
    j.from = function(t, e, i) {
        return i.runBackwards = !0,
        i.immediateRender = 0 != i.immediateRender,
        new j(t,e,i)
    }
    ,
    j.fromTo = function(t, e, i, n) {
        return n.startAt = i,
        n.immediateRender = 0 != n.immediateRender && 0 != i.immediateRender,
        new j(t,e,n)
    }
    ,
    j.delayedCall = function(t, e, i, n, r) {
        return new j(e,0,{
            delay: t,
            onComplete: e,
            onCompleteParams: i,
            callbackScope: n,
            onReverseComplete: e,
            onReverseCompleteParams: i,
            immediateRender: !1,
            lazy: !1,
            useFrames: r,
            overwrite: 0
        })
    }
    ,
    j.set = function(t, e) {
        return new j(t,0,e)
    }
    ,
    j.getTweensOf = function(t, e) {
        if (null == t)
            return [];
        var i, n, r, o;
        if (t = "string" != typeof t ? t : j.selector(t) || t,
        (b(t) || z(t)) && "number" != typeof t[0]) {
            for (i = t.length,
            n = []; -1 < --i; )
                n = n.concat(j.getTweensOf(t[i], e));
            for (i = n.length; -1 < --i; )
                for (o = n[i],
                r = i; -1 < --r; )
                    o === n[r] && n.splice(i, 1)
        } else if (t._gsTweenID)
            for (i = (n = tt(t).concat()).length; -1 < --i; )
                (n[i]._gc || e && !n[i].isActive()) && n.splice(i, 1);
        return n || []
    }
    ,
    j.killTweensOf = j.killDelayedCallsTo = function(t, e, i) {
        "object" == typeof e && (i = e,
        e = !1);
        for (var n = j.getTweensOf(t, e), r = n.length; -1 < --r; )
            n[r]._kill(i, t)
    }
    ;
    var rt = C("plugins.TweenPlugin", function(t, e) {
        this._overwriteProps = (t || "").split(","),
        this._propName = this._overwriteProps[0],
        this._priority = e || 0,
        this._super = rt.prototype
    }, !0);
    if (r = rt.prototype,
    rt.version = "1.19.0",
    rt.API = 2,
    r._firstPT = null,
    r._addTween = W,
    r.setRatio = L,
    r._kill = function(t) {
        var e, i = this._overwriteProps, n = this._firstPT;
        if (null != t[this._propName])
            this._overwriteProps = [];
        else
            for (e = i.length; -1 < --e; )
                null != t[i[e]] && i.splice(e, 1);
        for (; n; )
            null != t[n.n] && (n._next && (n._next._prev = n._prev),
            n._prev ? (n._prev._next = n._next,
            n._prev = null) : this._firstPT === n && (this._firstPT = n._next)),
            n = n._next;
        return !1
    }
    ,
    r._mod = r._roundProps = function(t) {
        for (var e, i = this._firstPT; i; )
            (e = t[this._propName] || null != i.n && t[i.n.split(this._propName + "_").join("")]) && "function" == typeof e && (2 === i.f ? i.t._applyPT.m = e : i.m = e),
            i = i._next
    }
    ,
    j._onPluginEvent = function(t, e) {
        var i, n, r, o, s, a = e._firstPT;
        if ("_onInitAllProps" === t) {
            for (; a; ) {
                for (s = a._next,
                n = r; n && n.pr > a.pr; )
                    n = n._next;
                (a._prev = n ? n._prev : o) ? a._prev._next = a : r = a,
                (a._next = n) ? n._prev = a : o = a,
                a = s
            }
            a = e._firstPT = r
        }
        for (; a; )
            a.pg && "function" == typeof a.t[t] && a.t[t]() && (i = !0),
            a = a._next;
        return i
    }
    ,
    rt.activate = function(t) {
        for (var e = t.length; -1 < --e; )
            t[e].API === rt.API && (U[(new t[e])._propName] = t[e]);
        return !0
    }
    ,
    a.plugin = function(t) {
        if (!(t && t.propName && t.init && t.API))
            throw "illegal plugin definition.";
        var e, i = t.propName, n = t.priority || 0, r = t.overwriteProps, o = {
            init: "_onInitTween",
            set: "setRatio",
            kill: "_kill",
            round: "_mod",
            mod: "_mod",
            initAll: "_onInitAllProps"
        }, s = C("plugins." + i.charAt(0).toUpperCase() + i.substr(1) + "Plugin", function() {
            rt.call(this, i, n),
            this._overwriteProps = r || []
        }, !0 === t.global), a = s.prototype = new rt(i);
        for (e in (a.constructor = s).API = t.API,
        o)
            "function" == typeof t[e] && (a[o[e]] = t[e]);
        return s.version = t.version,
        rt.activate([s]),
        s
    }
    ,
    e = f._gsQueue) {
        for (i = 0; i < e.length; i++)
            e[i]();
        for (r in x)
            x[r].func || f.console.log("GSAP encountered missing dependency: " + r)
    }
    m = !1
}("undefined" != typeof module && module.exports && "undefined" != typeof global ? global : this || window, "TweenMax"),
function(t, e) {
    "function" == typeof define && define.amd ? define(e) : "object" == typeof exports ? module.exports = e() : t.ScrollMagic = e()
}(this, function() {
    "use strict";
    var M = function() {
        F.log(2, "(COMPATIBILITY NOTICE) -> As of ScrollMagic 2.0.0 you need to use 'new ScrollMagic.Controller()' to create a new controller instance. Use 'new ScrollMagic.Scene()' to instance a scene.")
    };
    M.version = "2.0.6",
    window.addEventListener("mousewheel", function() {});
    var j = "data-scrollmagic-pin-spacer";
    M.Controller = function(t) {
        var i, n, r = "ScrollMagic.Controller", o = C.defaults, s = this, a = F.extend({}, o, t), l = [], u = !1, c = 0, h = "PAUSED", f = !0, d = 0, p = !0, g = function() {
            0 < a.refreshInterval && (n = window.setTimeout(b, a.refreshInterval))
        }, e = function() {
            return a.vertical ? F.get.scrollTop(a.container) : F.get.scrollLeft(a.container)
        }, _ = function() {
            return a.vertical ? F.get.height(a.container) : F.get.width(a.container)
        }, m = this._setScrollPos = function(t) {
            a.vertical ? f ? window.scrollTo(F.get.scrollLeft(), t) : a.container.scrollTop = t : f ? window.scrollTo(t, F.get.scrollTop()) : a.container.scrollLeft = t
        }
        , v = function() {
            if (p && u) {
                var i = F.type.Array(u) ? u : l.slice(0);
                u = !1;
                var t = c
                  , e = (c = s.scrollPos()) - t;
                0 !== e && (h = 0 < e ? "FORWARD" : "REVERSE"),
                "REVERSE" === h && i.reverse(),
                i.forEach(function(t, e) {
                    x(3, "updating Scene " + (e + 1) + "/" + i.length + " (" + l.length + " total)"),
                    t.update(!0)
                }),
                0 === i.length && 3 <= a.loglevel && x(3, "updating 0 Scenes (nothing added to controller)")
            }
        }, y = function() {
            i = F.rAF(v)
        }, w = function(t) {
            x(3, "event fired causing an update:", t.type),
            "resize" == t.type && (d = _(),
            h = "PAUSED"),
            !0 !== u && (u = !0,
            y())
        }, b = function() {
            if (!f && d != _()) {
                var e;
                try {
                    e = new Event("resize",{
                        bubbles: !1,
                        cancelable: !1
                    })
                } catch (t) {
                    (e = document.createEvent("Event")).initEvent("resize", !1, !1)
                }
                a.container.dispatchEvent(e)
            }
            l.forEach(function(t, e) {
                t.refresh()
            }),
            g()
        }, x = this._log = function(t, e) {
            a.loglevel >= t && (Array.prototype.splice.call(arguments, 1, 0, "(" + r + ") ->"),
            F.log.apply(window, arguments))
        }
        ;
        this._options = a;
        var T = function(t) {
            if (t.length <= 1)
                return t;
            var e = t.slice(0);
            return e.sort(function(t, e) {
                return t.scrollOffset() > e.scrollOffset() ? 1 : -1
            }),
            e
        };
        return this.addScene = function(t) {
            if (F.type.Array(t))
                t.forEach(function(t, e) {
                    s.addScene(t)
                });
            else if (t instanceof M.Scene) {
                if (t.controller() !== s)
                    t.addTo(s);
                else if (l.indexOf(t) < 0) {
                    for (var e in l.push(t),
                    l = T(l),
                    t.on("shift.controller_sort", function() {
                        l = T(l)
                    }),
                    a.globalSceneOptions)
                        t[e] && t[e].call(t, a.globalSceneOptions[e]);
                    x(3, "adding Scene (now " + l.length + " total)")
                }
            } else
                x(1, "ERROR: invalid argument supplied for '.addScene()'");
            return s
        }
        ,
        this.removeScene = function(t) {
            if (F.type.Array(t))
                t.forEach(function(t, e) {
                    s.removeScene(t)
                });
            else {
                var e = l.indexOf(t);
                -1 < e && (t.off("shift.controller_sort"),
                l.splice(e, 1),
                x(3, "removing Scene (now " + l.length + " left)"),
                t.remove())
            }
            return s
        }
        ,
        this.updateScene = function(t, i) {
            return F.type.Array(t) ? t.forEach(function(t, e) {
                s.updateScene(t, i)
            }) : i ? t.update(!0) : !0 !== u && t instanceof M.Scene && (-1 == (u = u || []).indexOf(t) && u.push(t),
            u = T(u),
            y()),
            s
        }
        ,
        this.update = function(t) {
            return w({
                type: "resize"
            }),
            t && v(),
            s
        }
        ,
        this.scrollTo = function(t, e) {
            if (F.type.Number(t))
                m.call(a.container, t, e);
            else if (t instanceof M.Scene)
                t.controller() === s ? s.scrollTo(t.scrollOffset(), e) : x(2, "scrollTo(): The supplied scene does not belong to this controller. Scroll cancelled.", t);
            else if (F.type.Function(t))
                m = t;
            else {
                var i = F.get.elements(t)[0];
                if (i) {
                    for (; i.parentNode.hasAttribute(j); )
                        i = i.parentNode;
                    var n = a.vertical ? "top" : "left"
                      , r = F.get.offset(a.container)
                      , o = F.get.offset(i);
                    f || (r[n] -= s.scrollPos()),
                    s.scrollTo(o[n] - r[n], e)
                } else
                    x(2, "scrollTo(): The supplied argument is invalid. Scroll cancelled.", t)
            }
            return s
        }
        ,
        this.scrollPos = function(t) {
            return arguments.length ? (F.type.Function(t) ? e = t : x(2, "Provided value for method 'scrollPos' is not a function. To change the current scroll position use 'scrollTo()'."),
            s) : e.call(s)
        }
        ,
        this.info = function(t) {
            var e = {
                size: d,
                vertical: a.vertical,
                scrollPos: c,
                scrollDirection: h,
                container: a.container,
                isDocument: f
            };
            return arguments.length ? void 0 !== e[t] ? e[t] : void x(1, 'ERROR: option "' + t + '" is not available') : e
        }
        ,
        this.loglevel = function(t) {
            return arguments.length ? (a.loglevel != t && (a.loglevel = t),
            s) : a.loglevel
        }
        ,
        this.enabled = function(t) {
            return arguments.length ? (p != t && (p = !!t,
            s.updateScene(l, !0)),
            s) : p
        }
        ,
        this.destroy = function(t) {
            window.clearTimeout(n);
            for (var e = l.length; e--; )
                l[e].destroy(t);
            return a.container.removeEventListener("resize", w),
            a.container.removeEventListener("scroll", w),
            F.cAF(i),
            x(3, "destroyed " + r + " (reset: " + (t ? "true" : "false") + ")"),
            null
        }
        ,
        function() {
            for (var t in a)
                o.hasOwnProperty(t) || (x(2, 'WARNING: Unknown option "' + t + '"'),
                delete a[t]);
            if (a.container = F.get.elements(a.container)[0],
            !a.container)
                throw x(1, "ERROR creating object " + r + ": No valid scroll container supplied"),
                r + " init failed.";
            (f = a.container === window || a.container === document.body || !document.body.contains(a.container)) && (a.container = window),
            d = _(),
            a.container.addEventListener("resize", w),
            a.container.addEventListener("scroll", w);
            var e = parseInt(a.refreshInterval, 10);
            a.refreshInterval = F.type.Number(e) ? e : o.refreshInterval,
            g(),
            x(3, "added new " + r + " controller (v" + M.version + ")")
        }(),
        s
    }
    ;
    var C = {
        defaults: {
            container: window,
            vertical: !0,
            globalSceneOptions: {},
            loglevel: 2,
            refreshInterval: 100
        }
    };
    M.Controller.addOption = function(t, e) {
        C.defaults[t] = e
    }
    ,
    M.Controller.extend = function(t) {
        var e = this;
        M.Controller = function() {
            return e.apply(this, arguments),
            this.$super = F.extend({}, this),
            t.apply(this, arguments) || this
        }
        ,
        F.extend(M.Controller, e),
        M.Controller.prototype = e.prototype,
        M.Controller.prototype.constructor = M.Controller
    }
    ,
    M.Scene = function(t) {
        var i, l, n = "ScrollMagic.Scene", u = "BEFORE", c = "DURING", h = "AFTER", r = z.defaults, f = this, d = F.extend({}, r, t), p = u, g = 0, a = {
            start: 0,
            end: 0
        }, _ = 0, o = !0, s = {};
        this.on = function(t, r) {
            return F.type.Function(r) ? (t = t.trim().split(" ")).forEach(function(t) {
                var e = t.split(".")
                  , i = e[0]
                  , n = e[1];
                "*" != i && (s[i] || (s[i] = []),
                s[i].push({
                    namespace: n || "",
                    callback: r
                }))
            }) : m(1, "ERROR when calling '.on()': Supplied callback for '" + t + "' is not a valid function!"),
            f
        }
        ,
        this.off = function(t, o) {
            return t ? (t = t.trim().split(" ")).forEach(function(t, e) {
                var i = t.split(".")
                  , n = i[0]
                  , r = i[1] || "";
                ("*" === n ? Object.keys(s) : [n]).forEach(function(t) {
                    for (var e = s[t] || [], i = e.length; i--; ) {
                        var n = e[i];
                        !n || r !== n.namespace && "*" !== r || o && o != n.callback || e.splice(i, 1)
                    }
                    e.length || delete s[t]
                })
            }) : m(1, "ERROR: Invalid event name supplied."),
            f
        }
        ,
        this.trigger = function(t, i) {
            if (t) {
                var e = t.trim().split(".")
                  , n = e[0]
                  , r = e[1]
                  , o = s[n];
                m(3, "event fired:", n, i ? "->" : "", i || ""),
                o && o.forEach(function(t, e) {
                    r && r !== t.namespace || t.callback.call(f, new M.Event(n,t.namespace,f,i))
                })
            } else
                m(1, "ERROR: Invalid event name supplied.");
            return f
        }
        ,
        f.on("change.internal", function(t) {
            "loglevel" !== t.what && "tweenChanges" !== t.what && ("triggerElement" === t.what ? b() : "reverse" === t.what && f.update())
        }).on("shift.internal", function(t) {
            e(),
            f.update()
        });
        var m = this._log = function(t, e) {
            d.loglevel >= t && (Array.prototype.splice.call(arguments, 1, 0, "(" + n + ") ->"),
            F.log.apply(window, arguments))
        }
        ;
        this.addTo = function(t) {
            return t instanceof M.Controller ? l != t && (l && l.removeScene(f),
            l = t,
            C(),
            w(!0),
            b(!0),
            e(),
            l.info("container").addEventListener("resize", x),
            t.addScene(f),
            f.trigger("add", {
                controller: l
            }),
            m(3, "added " + n + " to controller"),
            f.update()) : m(1, "ERROR: supplied argument of 'addTo()' is not a valid ScrollMagic Controller"),
            f
        }
        ,
        this.enabled = function(t) {
            return arguments.length ? (o != t && (o = !!t,
            f.update(!0)),
            f) : o
        }
        ,
        this.remove = function() {
            if (l) {
                l.info("container").removeEventListener("resize", x);
                var t = l;
                l = void 0,
                t.removeScene(f),
                f.trigger("remove"),
                m(3, "removed " + n + " from controller")
            }
            return f
        }
        ,
        this.destroy = function(t) {
            return f.trigger("destroy", {
                reset: t
            }),
            f.remove(),
            f.off("*.*"),
            m(3, "destroyed " + n + " (reset: " + (t ? "true" : "false") + ")"),
            null
        }
        ,
        this.update = function(t) {
            if (l)
                if (t)
                    if (l.enabled() && o) {
                        var e, i = l.info("scrollPos");
                        e = 0 < d.duration ? (i - a.start) / (a.end - a.start) : i >= a.start ? 1 : 0,
                        f.trigger("update", {
                            startPos: a.start,
                            endPos: a.end,
                            scrollPos: i
                        }),
                        f.progress(e)
                    } else
                        v && p === c && $(!0);
                else
                    l.updateScene(f, !1);
            return f
        }
        ,
        this.refresh = function() {
            return w(),
            b(),
            f
        }
        ,
        this.progress = function(t) {
            if (arguments.length) {
                var e = !1
                  , i = p
                  , n = l ? l.info("scrollDirection") : "PAUSED"
                  , r = d.reverse || g <= t;
                if (0 === d.duration ? (e = g != t,
                p = 0 === (g = t < 1 && r ? 0 : 1) ? u : c) : t < 0 && p !== u && r ? (p = u,
                e = !(g = 0)) : 0 <= t && t < 1 && r ? (g = t,
                p = c,
                e = !0) : 1 <= t && p !== h ? (g = 1,
                p = h,
                e = !0) : p !== c || r || $(),
                e) {
                    var o = {
                        progress: g,
                        state: p,
                        scrollDirection: n
                    }
                      , s = p != i
                      , a = function(t) {
                        f.trigger(t, o)
                    };
                    s && i !== c && (a("enter"),
                    a(i === u ? "start" : "end")),
                    a("progress"),
                    s && p !== c && (a(p === u ? "start" : "end"),
                    a("leave"))
                }
                return f
            }
            return g
        }
        ;
        var v, y, e = function() {
            a = {
                start: _ + d.offset
            },
            l && d.triggerElement && (a.start -= l.info("size") * d.triggerHook),
            a.end = a.start + d.duration
        }, w = function(t) {
            if (i) {
                var e = "duration";
                k(e, i.call(f)) && !t && (f.trigger("change", {
                    what: e,
                    newval: d[e]
                }),
                f.trigger("shift", {
                    reason: e
                }))
            }
        }, b = function(t) {
            var e = 0
              , i = d.triggerElement;
            if (l && (i || 0 < _)) {
                if (i)
                    if (i.parentNode) {
                        for (var n = l.info(), r = F.get.offset(n.container), o = n.vertical ? "top" : "left"; i.parentNode.hasAttribute(j); )
                            i = i.parentNode;
                        var s = F.get.offset(i);
                        n.isDocument || (r[o] -= l.scrollPos()),
                        e = s[o] - r[o]
                    } else
                        m(2, "WARNING: triggerElement was removed from DOM and will be reset to", void 0),
                        f.triggerElement(void 0);
                var a = e != _;
                _ = e,
                a && !t && f.trigger("shift", {
                    reason: "triggerElementPosition"
                })
            }
        }, x = function(t) {
            0 < d.triggerHook && f.trigger("shift", {
                reason: "containerResize"
            })
        }, T = F.extend(z.validate, {
            duration: function(e) {
                if (F.type.String(e) && e.match(/^(\.|\d)*\d+%$/)) {
                    var t = parseFloat(e) / 100;
                    e = function() {
                        return l ? l.info("size") * t : 0
                    }
                }
                if (F.type.Function(e)) {
                    i = e;
                    try {
                        e = parseFloat(i())
                    } catch (t) {
                        e = -1
                    }
                }
                if (e = parseFloat(e),
                !F.type.Number(e) || e < 0)
                    throw i ? (i = void 0,
                    ['Invalid return value of supplied function for option "duration":', e]) : ['Invalid value for option "duration":', e];
                return e
            }
        }), C = function(t) {
            (t = arguments.length ? [t] : Object.keys(T)).forEach(function(e, t) {
                var i;
                if (T[e])
                    try {
                        i = T[e](d[e])
                    } catch (t) {
                        i = r[e];
                        var n = F.type.String(t) ? [t] : t;
                        F.type.Array(n) ? (n[0] = "ERROR: " + n[0],
                        n.unshift(1),
                        m.apply(this, n)) : m(1, "ERROR: Problem executing validation callback for option '" + e + "':", t.message)
                    } finally {
                        d[e] = i
                    }
            })
        }, k = function(t, e) {
            var i = !1
              , n = d[t];
            return d[t] != e && (d[t] = e,
            C(t),
            i = n != d[t]),
            i
        }, S = function(e) {
            f[e] || (f[e] = function(t) {
                return arguments.length ? ("duration" === e && (i = void 0),
                k(e, t) && (f.trigger("change", {
                    what: e,
                    newval: d[e]
                }),
                -1 < z.shifts.indexOf(e) && f.trigger("shift", {
                    reason: e
                })),
                f) : d[e]
            }
            )
        };
        this.controller = function() {
            return l
        }
        ,
        this.state = function() {
            return p
        }
        ,
        this.scrollOffset = function() {
            return a.start
        }
        ,
        this.triggerPosition = function() {
            var t = d.offset;
            return l && (d.triggerElement ? t += _ : t += l.info("size") * f.triggerHook()),
            t
        }
        ,
        f.on("shift.internal", function(t) {
            var e = "duration" === t.reason;
            (p === h && e || p === c && 0 === d.duration) && $(),
            e && D()
        }).on("progress.internal", function(t) {
            $()
        }).on("add.internal", function(t) {
            D()
        }).on("destroy.internal", function(t) {
            f.removePin(t.reset)
        });
        var $ = function(t) {
            if (v && l) {
                var e = l.info()
                  , i = y.spacer.firstChild;
                if (t || p !== c) {
                    var n = {
                        position: y.inFlow ? "relative" : "absolute",
                        top: 0,
                        left: 0
                    }
                      , r = F.css(i, "position") != n.position;
                    y.pushFollowers ? 0 < d.duration && (p === h && 0 === parseFloat(F.css(y.spacer, "padding-top")) ? r = !0 : p === u && 0 === parseFloat(F.css(y.spacer, "padding-bottom")) && (r = !0)) : n[e.vertical ? "top" : "left"] = d.duration * g,
                    F.css(i, n),
                    r && D()
                } else {
                    "fixed" != F.css(i, "position") && (F.css(i, {
                        position: "fixed"
                    }),
                    D());
                    var o = F.get.offset(y.spacer, !0)
                      , s = d.reverse || 0 === d.duration ? e.scrollPos - a.start : Math.round(g * d.duration * 10) / 10;
                    o[e.vertical ? "top" : "left"] += s,
                    F.css(y.spacer.firstChild, {
                        top: o.top,
                        left: o.left
                    })
                }
            }
        }
          , D = function() {
            if (v && l && y.inFlow) {
                var t = p === c
                  , e = l.info("vertical")
                  , i = y.spacer.firstChild
                  , n = F.isMarginCollapseType(F.css(y.spacer, "display"))
                  , r = {};
                y.relSize.width || y.relSize.autoFullWidth ? t ? F.css(v, {
                    width: F.get.width(y.spacer)
                }) : F.css(v, {
                    width: "100%"
                }) : (r["min-width"] = F.get.width(e ? v : i, !0, !0),
                r.width = t ? r["min-width"] : "auto"),
                y.relSize.height ? t ? F.css(v, {
                    height: F.get.height(y.spacer) - (y.pushFollowers ? d.duration : 0)
                }) : F.css(v, {
                    height: "100%"
                }) : (r["min-height"] = F.get.height(e ? i : v, !0, !n),
                r.height = t ? r["min-height"] : "auto"),
                y.pushFollowers && (r["padding" + (e ? "Top" : "Left")] = d.duration * g,
                r["padding" + (e ? "Bottom" : "Right")] = d.duration * (1 - g)),
                F.css(y.spacer, r)
            }
        }
          , A = function() {
            l && v && p === c && !l.info("isDocument") && $()
        }
          , E = function() {
            l && v && p === c && ((y.relSize.width || y.relSize.autoFullWidth) && F.get.width(window) != F.get.width(y.spacer.parentNode) || y.relSize.height && F.get.height(window) != F.get.height(y.spacer.parentNode)) && D()
        }
          , P = function(t) {
            l && v && p === c && !l.info("isDocument") && (t.preventDefault(),
            l._setScrollPos(l.info("scrollPos") - ((t.wheelDelta || t[l.info("vertical") ? "wheelDeltaY" : "wheelDeltaX"]) / 3 || 30 * -t.detail)))
        };
        this.setPin = function(t, e) {
            if (e = F.extend({}, {
                pushFollowers: !0,
                spacerClass: "scrollmagic-pin-spacer"
            }, e),
            !(t = F.get.elements(t)[0]))
                return m(1, "ERROR calling method 'setPin()': Invalid pin element supplied."),
                f;
            if ("fixed" === F.css(t, "position"))
                return m(1, "ERROR calling method 'setPin()': Pin does not work with elements that are positioned 'fixed'."),
                f;
            if (v) {
                if (v === t)
                    return f;
                f.removePin()
            }
            var i = (v = t).parentNode.style.display
              , n = ["top", "left", "bottom", "right", "margin", "marginLeft", "marginRight", "marginTop", "marginBottom"];
            v.parentNode.style.display = "none";
            var r = "absolute" != F.css(v, "position")
              , o = F.css(v, n.concat(["display"]))
              , s = F.css(v, ["width", "height"]);
            v.parentNode.style.display = i,
            !r && e.pushFollowers && (m(2, "WARNING: If the pinned element is positioned absolutely pushFollowers will be disabled."),
            e.pushFollowers = !1),
            window.setTimeout(function() {
                v && 0 === d.duration && e.pushFollowers && m(2, "WARNING: pushFollowers =", !0, "has no effect, when scene duration is 0.")
            }, 0);
            var a = v.parentNode.insertBefore(document.createElement("div"), v)
              , l = F.extend(o, {
                position: r ? "relative" : "absolute",
                boxSizing: "content-box",
                mozBoxSizing: "content-box",
                webkitBoxSizing: "content-box"
            });
            if (r || F.extend(l, F.css(v, ["width", "height"])),
            F.css(a, l),
            a.setAttribute(j, ""),
            F.addClass(a, e.spacerClass),
            y = {
                spacer: a,
                relSize: {
                    width: "%" === s.width.slice(-1),
                    height: "%" === s.height.slice(-1),
                    autoFullWidth: "auto" === s.width && r && F.isMarginCollapseType(o.display)
                },
                pushFollowers: e.pushFollowers,
                inFlow: r
            },
            !v.___origStyle) {
                v.___origStyle = {};
                var u = v.style;
                n.concat(["width", "height", "position", "boxSizing", "mozBoxSizing", "webkitBoxSizing"]).forEach(function(t) {
                    v.___origStyle[t] = u[t] || ""
                })
            }
            return y.relSize.width && F.css(a, {
                width: s.width
            }),
            y.relSize.height && F.css(a, {
                height: s.height
            }),
            a.appendChild(v),
            F.css(v, {
                position: r ? "relative" : "absolute",
                margin: "auto",
                top: "auto",
                left: "auto",
                bottom: "auto",
                right: "auto"
            }),
            (y.relSize.width || y.relSize.autoFullWidth) && F.css(v, {
                boxSizing: "border-box",
                mozBoxSizing: "border-box",
                webkitBoxSizing: "border-box"
            }),
            window.addEventListener("scroll", A),
            window.addEventListener("resize", A),
            window.addEventListener("resize", E),
            v.addEventListener("mousewheel", P),
            v.addEventListener("DOMMouseScroll", P),
            m(3, "added pin"),
            $(),
            f
        }
        ,
        this.removePin = function(t) {
            if (v) {
                if (p === c && $(!0),
                t || !l) {
                    var e = y.spacer.firstChild;
                    if (e.hasAttribute(j)) {
                        var i = y.spacer.style
                          , n = {};
                        ["margin", "marginLeft", "marginRight", "marginTop", "marginBottom"].forEach(function(t) {
                            n[t] = i[t] || ""
                        }),
                        F.css(e, n)
                    }
                    y.spacer.parentNode.insertBefore(e, y.spacer),
                    y.spacer.parentNode.removeChild(y.spacer),
                    v.parentNode.hasAttribute(j) || (F.css(v, v.___origStyle),
                    delete v.___origStyle)
                }
                window.removeEventListener("scroll", A),
                window.removeEventListener("resize", A),
                window.removeEventListener("resize", E),
                v.removeEventListener("mousewheel", P),
                v.removeEventListener("DOMMouseScroll", P),
                v = void 0,
                m(3, "removed pin (reset: " + (t ? "true" : "false") + ")")
            }
            return f
        }
        ;
        var O, R = [];
        return f.on("destroy.internal", function(t) {
            f.removeClassToggle(t.reset)
        }),
        this.setClassToggle = function(t, e) {
            var i = F.get.elements(t);
            return 0 !== i.length && F.type.String(e) ? (0 < R.length && f.removeClassToggle(),
            O = e,
            R = i,
            f.on("enter.internal_class leave.internal_class", function(t) {
                var i = "enter" === t.type ? F.addClass : F.removeClass;
                R.forEach(function(t, e) {
                    i(t, O)
                })
            })) : m(1, "ERROR calling method 'setClassToggle()': Invalid " + (0 === i.length ? "element" : "classes") + " supplied."),
            f
        }
        ,
        this.removeClassToggle = function(t) {
            return t && R.forEach(function(t, e) {
                F.removeClass(t, O)
            }),
            f.off("start.internal_class end.internal_class"),
            O = void 0,
            R = [],
            f
        }
        ,
        function() {
            for (var t in d)
                r.hasOwnProperty(t) || (m(2, 'WARNING: Unknown option "' + t + '"'),
                delete d[t]);
            for (var e in r)
                S(e);
            C()
        }(),
        f
    }
    ;
    var z = {
        defaults: {
            duration: 0,
            offset: 0,
            triggerElement: void 0,
            triggerHook: .5,
            reverse: !0,
            loglevel: 2
        },
        validate: {
            offset: function(t) {
                if (t = parseFloat(t),
                !F.type.Number(t))
                    throw ['Invalid value for option "offset":', t];
                return t
            },
            triggerElement: function(t) {
                if (t = t || void 0) {
                    var e = F.get.elements(t)[0];
                    if (!e || !e.parentNode)
                        throw ['Element defined in option "triggerElement" was not found:', t];
                    t = e
                }
                return t
            },
            triggerHook: function(t) {
                var e = {
                    onCenter: .5,
                    onEnter: 1,
                    onLeave: 0
                };
                if (F.type.Number(t))
                    t = Math.max(0, Math.min(parseFloat(t), 1));
                else {
                    if (!(t in e))
                        throw ['Invalid value for option "triggerHook": ', t];
                    t = e[t]
                }
                return t
            },
            reverse: function(t) {
                return !!t
            },
            loglevel: function(t) {
                if (t = parseInt(t),
                !F.type.Number(t) || t < 0 || 3 < t)
                    throw ['Invalid value for option "loglevel":', t];
                return t
            }
        },
        shifts: ["duration", "offset", "triggerHook"]
    };
    M.Scene.addOption = function(t, e, i, n) {
        t in z.defaults ? M._util.log(1, "[static] ScrollMagic.Scene -> Cannot add Scene option '" + t + "', because it already exists.") : (z.defaults[t] = e,
        z.validate[t] = i,
        n && z.shifts.push(t))
    }
    ,
    M.Scene.extend = function(t) {
        var e = this;
        M.Scene = function() {
            return e.apply(this, arguments),
            this.$super = F.extend({}, this),
            t.apply(this, arguments) || this
        }
        ,
        F.extend(M.Scene, e),
        M.Scene.prototype = e.prototype,
        M.Scene.prototype.constructor = M.Scene
    }
    ,
    M.Event = function(t, e, i, n) {
        for (var r in n = n || {})
            this[r] = n[r];
        return this.type = t,
        this.target = this.currentTarget = i,
        this.namespace = e || "",
        this.timeStamp = this.timestamp = Date.now(),
        this
    }
    ;
    var F = M._util = function(s) {
        var i, t = {}, a = function(t) {
            return parseFloat(t) || 0
        }, l = function(t) {
            return t.currentStyle ? t.currentStyle : s.getComputedStyle(t)
        }, n = function(t, e, i, n) {
            if ((e = e === document ? s : e) === s)
                n = !1;
            else if (!p.DomElement(e))
                return 0;
            t = t.charAt(0).toUpperCase() + t.substr(1).toLowerCase();
            var r = (i ? e["offset" + t] || e["outer" + t] : e["client" + t] || e["inner" + t]) || 0;
            if (i && n) {
                var o = l(e);
                r += "Height" === t ? a(o.marginTop) + a(o.marginBottom) : a(o.marginLeft) + a(o.marginRight)
            }
            return r
        }, u = function(t) {
            return t.replace(/^[^a-z]+([a-z])/g, "$1").replace(/-([a-z])/g, function(t) {
                return t[1].toUpperCase()
            })
        };
        t.extend = function(t) {
            for (t = t || {},
            i = 1; i < arguments.length; i++)
                if (arguments[i])
                    for (var e in arguments[i])
                        arguments[i].hasOwnProperty(e) && (t[e] = arguments[i][e]);
            return t
        }
        ,
        t.isMarginCollapseType = function(t) {
            return -1 < ["block", "flex", "list-item", "table", "-webkit-box"].indexOf(t)
        }
        ;
        var r = 0
          , e = ["ms", "moz", "webkit", "o"]
          , o = s.requestAnimationFrame
          , c = s.cancelAnimationFrame;
        for (i = 0; !o && i < e.length; ++i)
            o = s[e[i] + "RequestAnimationFrame"],
            c = s[e[i] + "CancelAnimationFrame"] || s[e[i] + "CancelRequestAnimationFrame"];
        o || (o = function(t) {
            var e = (new Date).getTime()
              , i = Math.max(0, 16 - (e - r))
              , n = s.setTimeout(function() {
                t(e + i)
            }, i);
            return r = e + i,
            n
        }
        ),
        c || (c = function(t) {
            s.clearTimeout(t)
        }
        ),
        t.rAF = o.bind(s),
        t.cAF = c.bind(s);
        var h = ["error", "warn", "log"]
          , f = s.console || {};
        for (f.log = f.log || function() {}
        ,
        i = 0; i < h.length; i++) {
            var d = h[i];
            f[d] || (f[d] = f.log)
        }
        t.log = function(t) {
            (h.length < t || t <= 0) && (t = h.length);
            var e = new Date
              , i = ("0" + e.getHours()).slice(-2) + ":" + ("0" + e.getMinutes()).slice(-2) + ":" + ("0" + e.getSeconds()).slice(-2) + ":" + ("00" + e.getMilliseconds()).slice(-3)
              , n = h[t - 1]
              , r = Array.prototype.splice.call(arguments, 1)
              , o = Function.prototype.bind.call(f[n], f);
            r.unshift(i),
            o.apply(f, r)
        }
        ;
        var p = t.type = function(t) {
            return Object.prototype.toString.call(t).replace(/^\[object (.+)\]$/, "$1").toLowerCase()
        }
        ;
        p.String = function(t) {
            return "string" === p(t)
        }
        ,
        p.Function = function(t) {
            return "function" === p(t)
        }
        ,
        p.Array = function(t) {
            return Array.isArray(t)
        }
        ,
        p.Number = function(t) {
            return !p.Array(t) && 0 <= t - parseFloat(t) + 1
        }
        ,
        p.DomElement = function(t) {
            return "object" == typeof HTMLElement ? t instanceof HTMLElement : t && "object" == typeof t && null !== t && 1 === t.nodeType && "string" == typeof t.nodeName
        }
        ;
        var g = t.get = {};
        return g.elements = function(t) {
            var e = [];
            if (p.String(t))
                try {
                    t = document.querySelectorAll(t)
                } catch (t) {
                    return e
                }
            if ("nodelist" === p(t) || p.Array(t))
                for (var i = 0, n = e.length = t.length; i < n; i++) {
                    var r = t[i];
                    e[i] = p.DomElement(r) ? r : g.elements(r)
                }
            else
                (p.DomElement(t) || t === document || t === s) && (e = [t]);
            return e
        }
        ,
        g.scrollTop = function(t) {
            return t && "number" == typeof t.scrollTop ? t.scrollTop : s.pageYOffset || 0
        }
        ,
        g.scrollLeft = function(t) {
            return t && "number" == typeof t.scrollLeft ? t.scrollLeft : s.pageXOffset || 0
        }
        ,
        g.width = function(t, e, i) {
            return n("width", t, e, i)
        }
        ,
        g.height = function(t, e, i) {
            return n("height", t, e, i)
        }
        ,
        g.offset = function(t, e) {
            var i = {
                top: 0,
                left: 0
            };
            if (t && t.getBoundingClientRect) {
                var n = t.getBoundingClientRect();
                i.top = n.top,
                i.left = n.left,
                e || (i.top += g.scrollTop(),
                i.left += g.scrollLeft())
            }
            return i
        }
        ,
        t.addClass = function(t, e) {
            e && (t.classList ? t.classList.add(e) : t.className += " " + e)
        }
        ,
        t.removeClass = function(t, e) {
            e && (t.classList ? t.classList.remove(e) : t.className = t.className.replace(new RegExp("(^|\\b)" + e.split(" ").join("|") + "(\\b|$)","gi"), " "))
        }
        ,
        t.css = function(t, e) {
            if (p.String(e))
                return l(t)[u(e)];
            if (p.Array(e)) {
                var i = {}
                  , n = l(t);
                return e.forEach(function(t, e) {
                    i[t] = n[u(t)]
                }),
                i
            }
            for (var r in e) {
                var o = e[r];
                o == parseFloat(o) && (o += "px"),
                t.style[u(r)] = o
            }
        }
        ,
        t
    }(window || {});
    return M.Scene.prototype.addIndicators = function() {
        return M._util.log(1, "(ScrollMagic.Scene) -> ERROR calling addIndicators() due to missing Plugin 'debug.addIndicators'. Please make sure to include plugins/debug.addIndicators.js"),
        this
    }
    ,
    M.Scene.prototype.removeIndicators = function() {
        return M._util.log(1, "(ScrollMagic.Scene) -> ERROR calling removeIndicators() due to missing Plugin 'debug.addIndicators'. Please make sure to include plugins/debug.addIndicators.js"),
        this
    }
    ,
    M.Scene.prototype.setTween = function() {
        return M._util.log(1, "(ScrollMagic.Scene) -> ERROR calling setTween() due to missing Plugin 'animation.gsap'. Please make sure to include plugins/animation.gsap.js"),
        this
    }
    ,
    M.Scene.prototype.removeTween = function() {
        return M._util.log(1, "(ScrollMagic.Scene) -> ERROR calling removeTween() due to missing Plugin 'animation.gsap'. Please make sure to include plugins/animation.gsap.js"),
        this
    }
    ,
    M.Scene.prototype.setVelocity = function() {
        return M._util.log(1, "(ScrollMagic.Scene) -> ERROR calling setVelocity() due to missing Plugin 'animation.velocity'. Please make sure to include plugins/animation.velocity.js"),
        this
    }
    ,
    M.Scene.prototype.removeVelocity = function() {
        return M._util.log(1, "(ScrollMagic.Scene) -> ERROR calling removeVelocity() due to missing Plugin 'animation.velocity'. Please make sure to include plugins/animation.velocity.js"),
        this
    }
    ,
    M
}),
function(t, e) {
    "function" == typeof define && define.amd ? define(["ScrollMagic", "TweenMax", "TimelineMax"], e) : "object" == typeof exports ? (require("gsap"),
    e(require("scrollmagic"), TweenMax, TimelineMax)) : e(t.ScrollMagic || t.jQuery && t.jQuery.ScrollMagic, t.TweenMax || t.TweenLite, t.TimelineMax || t.TimelineLite)
}(this, function(t, g, _) {
    "use strict";
    var e = "animation.gsap"
      , i = window.console || {}
      , n = Function.prototype.bind.call(i.error || i.log || function() {}
    , i);
    t || n("(" + e + ") -> ERROR: The ScrollMagic main module could not be found. Please make sure it's loaded before this plugin or use an asynchronous loader like requirejs."),
    g || n("(" + e + ") -> ERROR: TweenLite or TweenMax could not be found. Please make sure GSAP is loaded before ScrollMagic or use an asynchronous loader like requirejs."),
    t.Scene.addOption("tweenChanges", !1, function(t) {
        return !!t
    }),
    t.Scene.extend(function() {
        var h, f = this, d = function() {
            f._log && (Array.prototype.splice.call(arguments, 1, 0, "(" + e + ")", "->"),
            f._log.apply(this, arguments))
        };
        f.on("progress.plugin_gsap", function() {
            p()
        }),
        f.on("destroy.plugin_gsap", function(t) {
            f.removeTween(t.reset)
        });
        var p = function() {
            if (h) {
                var t = f.progress()
                  , e = f.state();
                h.repeat && -1 === h.repeat() ? "DURING" === e && h.paused() ? h.play() : "DURING" === e || h.paused() || h.pause() : t != h.progress() && (0 === f.duration() ? 0 < t ? h.play() : h.reverse() : f.tweenChanges() && h.tweenTo ? h.tweenTo(t * h.duration()) : h.progress(t).pause())
            }
        };
        f.setTween = function(t, e, i) {
            var n;
            1 < arguments.length && (arguments.length < 3 && (i = e,
            e = 1),
            t = g.to(t, e, i));
            try {
                (n = _ ? new _({
                    smoothChildTiming: !0
                }).add(t) : t).pause()
            } catch (t) {
                return d(1, "ERROR calling method 'setTween()': Supplied argument is not a valid TweenObject"),
                f
            }
            if (h && f.removeTween(),
            h = n,
            t.repeat && -1 === t.repeat() && (h.repeat(-1),
            h.yoyo(t.yoyo())),
            f.tweenChanges() && !h.tweenTo && d(2, "WARNING: tweenChanges will only work if the TimelineMax object is available for ScrollMagic."),
            h && f.controller() && f.triggerElement() && 2 <= f.loglevel()) {
                var r = g.getTweensOf(f.triggerElement())
                  , o = f.controller().info("vertical");
                r.forEach(function(t, e) {
                    var i = t.vars.css || t.vars;
                    if (o ? void 0 !== i.top || void 0 !== i.bottom : void 0 !== i.left || void 0 !== i.right)
                        return d(2, "WARNING: Tweening the position of the trigger element affects the scene timing and should be avoided!"),
                        !1
                })
            }
            if (1.14 <= parseFloat(TweenLite.version))
                for (var s, a, l = h.getChildren ? h.getChildren(!0, !0, !1) : [h], u = function() {
                    d(2, "WARNING: tween was overwritten by another. To learn how to avoid this issue see here: https://github.com/janpaepke/ScrollMagic/wiki/WARNING:-tween-was-overwritten-by-another")
                }, c = 0; c < l.length; c++)
                    s = l[c],
                    a !== u && (a = s.vars.onOverwrite,
                    s.vars.onOverwrite = function() {
                        a && a.apply(this, arguments),
                        u.apply(this, arguments)
                    }
                    );
            return d(3, "added tween"),
            p(),
            f
        }
        ,
        f.removeTween = function(t) {
            return h && (t && h.progress(0).pause(),
            h.kill(),
            h = void 0,
            d(3, "removed tween (reset: " + (t ? "true" : "false") + ")")),
            f
        }
    })
});
var DB = DB || {};
function handleBarVisibility(t, e, i) {
    var n = $(window).scrollTop();
    if (e <= n && n < i)
        return t.addClass("c-contact-bar--active");
    t.removeClass("c-contact-bar--active")
}
DB.breakpoint = {
    globalSmallDesktop: "1024px",
    globalLargeDesktop: "1134px",
    mobileMin: "320px",
    mobileMax: "739px",
    tabletMin: "740px",
    tabletMax: "979px",
    desktopMin: "980px",
    desktopMax: "1299px",
    wide: "1300px"
},
DB.global = {
    defaultTransition: 300,
    defaultTransitionLong: 500,
    scrollToTimer: 400,
    festivalId: $("body").data("festival-id") - 1 || 0
},
DB.siteData = {
    festivalNameShort: ["CL", "EB", "DL", "SA", "DO", "M20/20 China"],
    festivalNameLong: ["Cannes Lions", "Eurobest", "Dubai Lynx", "Spikes Asia", "Demo", "Money20/20 China"],
    marketoScriptSrc: ["//app-e.marketo.com", "//app-e.marketo.com", "//app-e.marketo.com", "//app-e.marketo.com", "//app-e.marketo.com", "//app-lon07.marketo.com"],
    marketoMunchkinId: ["291-EHM-587", "291-EHM-587", "291-EHM-587", "291-EHM-587", "291-EHM-587", "652-GAM-809"]
},

DB.shapes = {
    static: function() {
        var t = $("[data-shapes-static]")
          , e = t.find("[data-shapes-wrapper]");
        if (0 !== t.length)
            return CL_BRANDING.isMobile() ? CL_BRANDING.renderStaticShapes(e, "mobile") : void CL_BRANDING.renderStaticShapes(e)
    },
    /*footer: function() {
        var t = $("[data-footer-shapes-trigger]")
          , e = $("[data-shapes-footer]")
          , i = e.find("[data-shapes-wrapper]");
        if (0 !== e.length) {
            var n = 0 === t.length ? e.offset().top : t.offset().top
              , r = !1
              , o = CL_BRANDING.renderFooterShapes(i);
            $(document).on("scroll", function() {
                var t = $(window).scrollTop();
                !r && n <= t ? (CL_BRANDING.animateFooterShapes(o),
                r = !0) : r && t < n - 200 && (CL_BRANDING.resetFooterAnimation(),
                r = !1)
            })
        }
    },*/
    dynamic: function() {
        var t = $("[data-shapes-dynamic]")
          , e = t.find("[data-shapes-wrapper]");
        if (0 !== t.length) {
            var i = t.data("shapes-set");
            return CL_BRANDING.isDesktop() ? CL_BRANDING.renderDynamicShapes(t[0], e, i) : CL_BRANDING.isTablet() ? CL_BRANDING.renderStaticShapes(e) : void CL_BRANDING.renderStaticShapes(e, "mobile")
        }
    }
};
var CL_BRANDING = function() {
    var t = "square"
      , e = "triangle"
      , i = "circle"
      , _ = "arc"
      , n = "square-o"
      , r = "circle-o"
      , m = ["#FF3264", "#BDB1F0", "#00C8E6", "#F9D616", "#F68E7B", "#090972"]
      , h = [t, "rect", e, i, _, n, r]
      , f = [t, e, i, _, n, r]
      , v = [[i, t], [e, r], [e, _], [i, n]]
      , y = {
        small: {
            min: 25,
            max: 30
        },
        medium: {
            min: 35,
            max: 55
        },
        large: {
            min: 60,
            max: 85
        }
    }
      , w = {
        desktop: [{
            left: "3%",
            top: "8%"
        }, {
            left: "18%",
            top: "-6%"
        }, {
            left: "15%",
            top: "28%"
        }, {
            left: "3%",
            top: "45%"
        }, {
            left: "22%",
            top: "57%"
        }, {
            left: "10%",
            top: "77%"
        }, {
            left: "78%",
            top: "-5%"
        }, {
            left: "90%",
            top: "-3%%"
        }, {
            left: "80%",
            top: "27%"
        }, {
            left: "88%",
            top: "37%"
        }, {
            left: "78%",
            top: "57%"
        }, {
            left: "88%",
            top: "77%"
        }],
        mobile: [{
            left: "5%",
            bottom: "22px"
        }, {
            left: "22%",
            bottom: "15px"
        }, {
            left: "45%",
            bottom: "15px"
        }, {
            left: "72%",
            bottom: "40px"
        }, {
            left: "85%",
            bottom: "20px"
        }, {
            left: "25%",
            bottom: "80px"
        }, {
            left: "55%",
            bottom: "70px"
        }, {
            left: "80%",
            bottom: "90px"
        }],
        footer: [{
            left: b(10, 20) + "%"
        }, {
            left: b(18, 30) + "%"
        }, {
            left: b(70, 75) + "%"
        }, {
            left: b(80, 87) + "%"
        }]
    }
      , c = {
        home: [{
            shape: "square",
            tween: {
                x: 460,
                y: 460,
                rotation: 0,
                transformOrigin: "50%",
                width: 100,
                height: 100
            }
        }, {
            shape: "square-o",
            tween: {
                x: -410,
                y: 380,
                rotation: 0,
                transformOrigin: "50%",
                width: 130,
                height: 130,
                borderWidth: 20
            }
        }, {
            shape: "triangle",
            tween: {
                x: 555,
                y: 550,
                rotation: 90,
                transformOrigin: "50%",
                borderLeftWidth: 25,
                borderRightWidth: 25,
                borderBottomWidth: 50
            }
        }, {
            shape: "triangle",
            tween: {
                x: -480,
                y: 490,
                rotation: 0,
                transformOrigin: "50%",
                borderLeftWidth: 30,
                borderRightWidth: 30,
                borderBottomWidth: 60
            }
        }, {
            shape: "triangle",
            tween: {
                x: -300,
                y: 200,
                rotation: 90,
                transformOrigin: "50%",
                borderLeftWidth: 14,
                borderRightWidth: 14,
                borderBottomWidth: 28
            }
        }, {
            shape: "triangle",
            tween: {
                x: -455,
                y: 415,
                rotation: 90,
                transformOrigin: "50%",
                borderLeftWidth: 14,
                borderRightWidth: 14,
                borderBottomWidth: 28
            }
        }, {
            shape: "circle",
            tween: {
                x: 310,
                y: 210
            }
        }, {
            shape: "circle-o",
            tween: {
                x: 410,
                y: 315,
                width: 80,
                height: 80,
                borderWidth: 13
            }
        }, {
            shape: "rect",
            tween: {
                x: -600,
                y: 420,
                rotation: 0,
                transformOrigin: "50%"
            }
        }, {
            shape: "arc",
            tween: {
                x: 290,
                y: 370,
                rotation: -45,
                transformOrigin: "50%"
            }
        }],
        attend: [{
            shape: "circle-o",
            tween: {
                x: 350,
                y: 365,
                width: 100,
                height: 100,
                borderWidth: 13
            }
        }, {
            shape: "square",
            tween: {
                x: 356,
                y: 678,
                rotation: 0,
                transformOrigin: "50%",
                width: 64,
                height: 64
            }
        }, {
            shape: "triangle",
            tween: {
                x: 497,
                y: 525,
                rotation: 125,
                transformOrigin: "50%"
            }
        }, {
            shape: "triangle",
            tween: {
                x: 331,
                y: 635,
                transformOrigin: "50%",
                borderLeftWidth: 25,
                borderRightWidth: 25,
                borderBottomWidth: 50
            }
        }, {
            shape: "circle",
            tween: {
                x: 485,
                y: 476,
                width: 24,
                height: 24
            }
        }, {
            shape: "arc",
            tween: {
                x: -402,
                y: 712,
                rotation: -55,
                transformOrigin: "50%"
            }
        }, {
            shape: "circle",
            tween: {
                x: -385,
                y: 522,
                width: 36,
                height: 36
            }
        }, {
            shape: "triangle",
            tween: {
                x: -465,
                y: 606,
                rotation: 115,
                transformOrigin: "50%",
                borderLeftWidth: 25,
                borderRightWidth: 25,
                borderBottomWidth: 50
            }
        }, {
            shape: "square",
            tween: {
                x: -463,
                y: 461,
                rotation: 45,
                width: 36,
                height: 36
            }
        }, {
            shape: "square-o",
            tween: {
                x: -426,
                y: 328,
                rotation: 40
            }
        }, {
            shape: "rect",
            tween: {
                x: -528,
                y: 436,
                rotation: 1
            }
        }]
    }
      , o = "footer-shape-"
      , s = []
      , a = CustomEase.create("custom", "M0,0,C0,0,0.049,0.675,0.085,1.115,0.122,1.498,0.156,1.34,0.16,1.322,0.189,1.193,0.203,1.133,0.23,1,0.25,1.042,0.266,1.116,0.286,1.116,0.316,1.116,0.314,1.072,0.38,1,0.454,1,1,1,1,1")
      , l = CustomEase.create("custom", "M0,0 C0,0.81 0.088,1.536 0.228,1.536 0.332,1.536 0.4,1.016 0.4,1.016 0.4,1.016 0.474,1.242 0.55,1.242 0.63,1.242 0.7,1.016 0.7,1.016 0.7,1.016 0.74,1.14 0.8,1.14 0.854,1.14 0.9,1 0.9,1 0.9,1 1,1 1,1");
    function b(t, e) {
        return Math.floor(Math.random() * (e - t + 1)) + t
    }
    function x(t, e, i) {
        var n = "";
        i = void 0 !== i ? ' id="' + i.toString() + '"' : "";
        switch (e.type) {
        case "rect":
            n = "background: " + e.color + "; width: " + 2 * e.size + "px; height: " + e.size / 2 + "px;";
            break;
        case "rect-i":
            n = "background: " + e.color + "; width: " + e.size / 2 + "px; height: " + 2 * e.size + "px;";
            break;
        case "arc":
            n = "border-width: " + e.size / 3 + "px; border-top-color: " + e.color + "; border-right-color:" + e.color + "; width: " + 1 * e.size + "px; height: " + 1 * e.size + "px;";
            break;
        case "triangle":
            n = "border-bottom: " + 1.2 * e.size + "px solid " + e.color + "; border-left-width: " + e.size / 2 * 1.2 + "px; border-right-width: " + e.size / 2 * 1.2 + "px;";
            break;
        case "square-o":
        case "circle-o":
            n = "border-width: " + e.size / 4.6 + "px; border-color: " + e.color + "; width: " + e.size + "px; height: " + e.size + "px;";
            break;
        default:
            n = "background: " + e.color + "; width: " + e.size + "px; height: " + e.size + "px;"
        }
        e.position && e.position.top && (n += "top: " + e.position.top + ";"),
        e.position && e.position.bottom && (n += "bottom: " + e.position.bottom + ";"),
        e.position && e.position.left && (n += "left: " + e.position.left + ";"),
        e.position && e.position.right && (n += "right: " + e.position.right + ";"),
        e.rotation && (n += "transform: rotate(" + e.rotation + "deg);"),
        t.append('<div class="[ c-shape c-shape--' + e.type + ' ]"' + i + ' style="' + n + '"></div>')
    }
    function u(t, e, i) {
        for (var n, r, o, s, a, l, u = m.slice(0), c = (r = (n = u).slice(0),
        function() {
            r.length < 1 && (r = n.slice(0));
            var t = Math.floor(Math.random() * r.length)
              , e = r[t];
            return r.splice(t, 1),
            e
        }
        ), h = [], f = T() ? "medium" : "large", d = 0; d < e; d++) {
            var p = v[d][b(0, 1)]
              , g = (o = p,
            void 0,
            a = (s = {
                size: f
            }) && void 0 !== s.color ? s.color : m[b(0, m.length - 1)],
            l = !(!s || void 0 === s.rotation) && b(0, 360),
            {
                type: o,
                color: a,
                size: s && void 0 !== s.size ? b(y[s.size].min, y[s.size].max) : b(y.small.min, y.small.max),
                rotation: l
            });
            g.position = w.footer[d],
            g.color = c(),
            g.position.bottom = CL_BRANDING.isMobile() ? 3.5 * -Math.pow(65 - g.size, 1.35) + "px" : 3.5 * -Math.pow(100 - g.size, 1.35) + "px",
            p === _ && (g.size *= .7,
            g.animationPosition = -Math.ceil(41 * g.size / 50)),
            x(t, g, i + d),
            h.push(g)
        }
        return x(t, {
            type: "rect"
        }, "rectAD"),
        h
    }
    function d(t) {
        t.empty()
    }
    function p() {
        var t = document.documentElement.clientWidth;
        return t <= 980 ? "mobile" : 980 < t && t < 1200 ? "tablet" : "desktop"
    }
    function T() {
        return "mobile" === p()
    }
    function g() {
        return "desktop" === p()
    }
    return {
        colors: m,
        breakPoint: p(),
        renderStaticShapes: function(t, e) {
            !function(t, e) {
                d(t),
                e = void 0 !== e ? w[e] : w.desktop;
                for (var i, n, r, o, s, a = g() ? h : f, l = 0, u = e.length; l < u; l++) {
                    var c = (i = {
                        rotation: !0
                    },
                    r = (n = a || h)[b(0, n.length - 1)],
                    o = m[b(0, m.length - 1)],
                    s = !!i.rotation && b(0, 360),
                    {
                        type: r,
                        color: o,
                        size: i && void 0 !== i.size ? b(y[i.size].min, y[i.size].max) : b(y.small.min, y.small.max),
                        rotation: s
                    });
                    c.position = e[l],
                    x(t, c)
                }
            }(t, e)
        },
        renderDynamicShapes: function(t, e, i) {
            !function(t, e, i) {
                d(e);
                var n = {
                    duration: 600,
                    triggerHook: .5,
                    defaultEase: Power2.easeIn
                }
                  , r = new ScrollMagic.Controller
                  , o = {
                    triggerElement: t,
                    duration: n.duration
                };
                TweenMax.defaultEase = n.defaultEase,
                new ScrollMagic.Scene(o).triggerHook(n.triggerHook).addTo(r);
                for (var s = void 0 !== c[i] ? c[i] : c.home, a = 0, l = s.length; a < l; a++) {
                    e.append('<div class="[ c-shape c-shape--' + s[a].shape + ' ]" id="shape-' + a + '"></div>');
                    var u = TweenMax.to("#shape-" + a, 1, s[a].tween);
                    new ScrollMagic.Scene(o).setTween(u).triggerHook(n.triggerHook).addTo(r)
                }
            }(t, e, i)
        },
        renderFooterShapes: function(t) {
            return u(t, 4, o)
        },
        animateFooterShapes: function(t) {
            setTimeout(function() {
                !function(t, e, i) {
                    s = [];
                    for (var n = 0; n < t; n++) {
                        var r = void 0 !== i[n].animationPosition ? i[n].animationPosition : 0
                          , o = void 0 !== i[n].animationSpeed ? i[n].animationSpeed : b(2, 3) / 2;
                        s.push(TweenMax.to("#" + e + n, o, {
                            bottom: r,
                            delay: b(0, 2) / 6,
                            ease: l
                        }))
                    }
                    s.push(TweenMax.to("#rectAD", 3, {
                        bottom: 0,
                        delay: .5,
                        ease: a
                    })),
                    s.push(TweenMax.to("#rectAD", .5, {
                        rotation: 0,
                        delay: .5,
                        ease: Power1.easeIn
                    }))
                }(4, o, t)
            }, 500)
        },
        resetFooterAnimation: function() {
            !function(t) {
                for (var e = 0, i = t.length; e < i; e++)
                    t[e].pause(0)
            }(s)
        },
        clearShapes: function(t) {
            d(t)
        },
        getCurrentBreakpoint: function() {
            return p()
        },
        isMobile: function() {
            return T()
        },
        isTablet: function() {
            return "tablet" === p()
        },
        isDesktop: function() {
            return g()
        }
    }
}();
$(document).ready(function() {
    "use strict";
    DB.shapes.static(),
    DB.shapes.dynamic(),
    DB.shapes.footer(),
    DB.mediaExtendable.extendText()
}),
$(window).resize(function() {
    "use strict";
    $("[data-header]").hasClass("c-header--search-active") && DB.header.extendSearch(),
    DB.contactBar.handleBar();
    var t = CL_BRANDING.getCurrentBreakpoint();
    CL_BRANDING.breakPoint !== t && (DB.shapes.static(),
    DB.shapes.dynamic()),
    CL_BRANDING.breakPoint = t
});
