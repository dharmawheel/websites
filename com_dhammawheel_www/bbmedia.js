// BB [media] Light v1.0 [26.12.2012]
// (C) 2012 Evgeny Vrublevsky <veg@tut.by>
// http://phpbbex.com/forum/viewtopic.php?t=36

(function() {
    var a = "media";
    var b = "audio";
    var k = "video";
    if (typeof bbmediajs != "undefined") {
        return
    }
    bbmediajs = true;
    var i = function(n, p, e, m) {
        m = jQuery.extend({
            frameborder: "0"
        }, m);
        var o = '<iframe style="vertical-align: bottom;" width="' + p + '" height="' + e + '" src="' + n + '" webkitallowfullscreen mozallowfullscreen allowfullscreen';
        jQuery.each(m, function(q, r) {
            o += " " + q + '="' + r + '"'
        });
        return o + "></iframe>"
    };
    var f = function(o, q, e, n) {
        n = jQuery.extend({
            allowscriptaccess: "never",
            allowfullscreen: "true"
        }, n);
        var p = ' width="' + q + '" height="' + e + '"';
        var r = '<object style="vertical-align: bottom;" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"' + p + '><param name="movie" value="' + o + '" />';
        var m = '<embed style="vertical-align: bottom;" type="application/x-shockwave-flash"' + p + ' src="' + o + '"';
        jQuery.each(n, function(s, t) {
            r += '<param name="' + s + '" value="' + t + '" />';
            m += " " + s + '="' + t + '"'
        });
        return r + m + "></embed></object>"
    };
    var l = function(q, C, A, w) {
        var G = (w == a);
        var y = (w == b);
        var s = (w == k);
        var v = (y || G);
        var D = (s || G);
        var t = false;
        var r = false;
        if (!C && !A) {
            t = true;
            r = true;
            C = 640;
            A = 360
        } else {
            if (!C) {
                t = true;
                C = parseInt(A * (16 / 9))
            } else {
                if (!A) {
                    r = true;
                    A = parseInt(C * (9 / 16))
                }
            }
        }
        var H;
        q = jQuery.trim(q);
        if (D && (H = q.match(/^https?:\/\/(?:www\.)?youtube(?:-nocookie)?\.com\/(?:playlist\?(?:.*&)?list=|embed\/videoseries\?(?:.*&)?list=|p\/|view_play_list\?(?:.*&)?p=)([-_\w\d]+)/i))) {
            if (r) {
                A += 30
            } else {
                if (t) {
                    C += 53
                }
            }
            return i("https://www.youtube.com/embed/videoseries?list=" + H[1], C, A)
        }
        if (D && (H = q.match(/^https?:\/\/(?:www\.)?(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:watch\?(?:.*&)?v=|embed\/|v\/))([-_\w\d]+)(?:.*(?:[&?]start|[?&#]t)=(?:(\d+)h)?(?:(\d+)m)?(\d+)?)?/i))) {
            if (r) {
                A += 30
            } else {
                if (t) {
                    C += 53
                }
            }
            var p = parseInt(H[2] ? H[2] : 0) * 3600 + parseInt(H[3] ? H[3] : 0) * 60 + parseInt(H[4] ? H[4] : 0);
            return i("https://www.youtube.com/embed/" + H[1] + (p ? "?start=" + p : ""), C, A)
        }
        if (D && (H = q.match(/^https?:\/\/(?:www\.)?(?:vimeo\.com|player\.vimeo\.com\/video)\/(\d+)/i))) {
            return i("https://player.vimeo.com/video/" + H[1], C, A)
        }
        if (D && (H = q.match(/^https?:\/\/(?:www\.)?dailymotion\.com\/(?:video|swf|embed\/video)\/([0-9a-z]+)/i))) {
            return i("https://www.dailymotion.com/embed/video/" + H[1], C, A)
        }
        if (D && (H = q.match(/^https?:\/\/(?:www\.)?ustream\.tv\/(?:channel\/|embed\/)?((?:recorded\/)?[0-9]+)/i))) {
            return i("https://www.ustream.tv/embed/" + H[1] + "?v=3&wmode=direct", C, A, {
                scrolling: "no"
            })
        }
        if (v && (H = q.match(/(^https?:\/\/soundcloud\.com\/[-_\w\d]+\/(?:sets\/)?[-_\w\d]+\/?$|https?(?::\/\/|%3A%2F%2F)api\.soundcloud\.com(?:\/|%2F)(?:tracks|playlists)(?:\/|%2F)\d+)/i))) {
            var E = !!H[0].match(/(\/|%2F)(sets|playlists)(\/|%2F)/i);
            return f("https://player.soundcloud.com/player.swf?show_comments=true&auto_play=false&color=ff7700&url=" + encodeURIComponent(decodeURIComponent(H[0])), (t ? "100%" : C), E ? 225 : 81)
        }
        if (v && (H = q.match(/^https?:\/\/(?:www\.)?promodj\.(?:com|ru)\/(?:[-_\w\d]+\/\w+|embed|download)\/(\d+)/i))) {
            return i("http://promodj.com/embed/" + H[1] + "/big", (t ? "100%" : C), 70)
        }
        if (G && (H = q.match(/^https?:\/\/(?:www\.)?dermandar\.com\/p\/([-_\d\w]+)/i))) {
            return f("http://static.dermandar.com/swf/Viewer.swf?v=1.4", C, A, {
                flashvars: "pano=" + H[1]
            })
        }
        if (G && (H = q.match(/^https?:\/\/maps\.google(?:\.com)?\.\w+\/(?:maps\/?)?(?:ms\/?)?\?((?:.*&)?(?:ll|spn|sll|sspn|z|msid)=.*)$/i))) {
            var n = (q.indexOf("panoid=") == -1) ? (H[1].replace(/&output=embed/, "") + "&output=embed") : (H[1].replace(/&(source=|output=sv)embed/g, "") + "&source=embed&output=svembed");
            return i("https://maps.google.com/?" + n, t ? 640 : C, r ? 480 : A)
        }
        var z = q.match(/\.(ogg|oga|opus|webma|mp3|aac|m4a|wav)(?:\s*;|$)/i);
        var u = q.match(/\.(ogv|webm|webmv|mp4|m4v)(?:\s*;|$)/i);
        if (G && (z || u) && !(z && u) || y && z || s && u) {
            y = (y || G && z);
            s = !y;
            var o = y ? b : k;
            var F = jQuery.extend({
                ogg: "ogg",
                webm: "webm",
                mp4: "mp4"
            }, y ? {
                oga: "ogg",
                opus: "opus",
                webma: "webm",
                mp3: "mpeg",
                aac: "aac",
                m4a: "mp4",
                wav: "wav"
            } : {
                ogv: "ogg",
                webmv: "webm",
                m4v: "mp4"
            });
            var B = q.split(/\s*;\s*/);
            var m = "";
            var e = "";
            var x = "";
            jQuery.each(B, function(J, I) {
                if (H = I.match(/^(?:https?:\/\/)?[^:"']*\.(ogg|oga|ogv|opus|webm|webma|webmv|mp3|aac|mp4|m4a|m4v|wav)$/i)) {
                    var K = H[1];
                    if (typeof F[K] == "undefined") {
                        m = "";
                        return false
                    }
                    var L = o + "/" + F[K];
                    m += '<source src="' + I + '" type="' + L + '">';
                    e += (e ? ", " : "") + '<a href="' + I + '">' + H[1].toUpperCase() + "</a>"
                } else {
                    if (s && !x && I.match(/^(?:https?:\/\/)?[^:"']*\.(png|jpg|gif|webp)$/i)) {
                        x = I
                    } else {
                        m = "";
                        return false
                    }
                }
            });
            if (m) {
                return (y ? "<audio controls>" : '<video width="' + C + '" height="' + A + '" controls' + (x ? ' poster="' + x + '">' : ">")) + m + e + (y ? "</audio>" : "</video>")
            }
        }
        return false
    };
    var c = function(n) {
        var o = n("html").attr("lang");
        if (!o) {
            o = n("title").text().match(/[\u0400-\u04FF]+/) ? "ru" : "en"
        } else {
            if (o.length > 2) {
                o = o.substring(0, 2)
            }
        }
        var m = function(t, q, s) {
            var p = "data:image/gif;base64,R0lGODlhDgAOALMAAP9dXf9sbP9SUv+lpf+8vP+0tP+srMwAAP/29v/Bwf9+fv90dP9kZP95ef/////g4CH5BAAAAAAALAAAAAAOAA4AQARL8MhJawE4ayPJ+ovjgAshDWiqop3ivrB5FEEdiHZQSIbgC4/HT8A5JI7I5LHTaDYQCGdDRrPhbLsZY8uAchlZgya4OQ3PgkFlTYkAADs=";
            var r = 'phpBB <a style="color: #105289; text-decoration: none;" href="http://phpbbex.com/forum/viewtopic.php?t=36" target="_blank">[' + q + "]</a>";
            t.html('<div style="height: 100%; background-color: #000;"><table style="width: 100%; height: 100%; border: 0; border-collapse: collapse; vertical-align: middle; text-align: center;"><tr><td><div style="width: 140px; min-height: 14px; font: 10px/10px Verdana; color: #fff; display: inline-block; padding-left: 18px; border: 12px solid #333; background: #333 url(' + p + ') no-repeat 0 center;">' + s + '</div></td></tr></table></div><div style="text-align: right; height: 14px; margin-top: -14px; padding-right: 2px; font: 10px/10px Verdana; color: #555;">' + r + "</div>");
            if (q != k) {
                t.css("width", "400px").css("height", "80px")
            }
        };
        var e = function(r, p) {
            var q;
            switch (o) {
                case "ru":
                    q = "Извините, этот URL не поддерживается";
                    break;
                case "uk":
                    q = "Вибачте, цей URL не підтримується";
                    break;
                default:
                    q = "Sorry, this URL is not supported";
                    break
            }
            m(r, p, q)
        };
        n(".bbaudio, .bbvideo, .bbmedia").each(function() {
            var u = n(this);
            var v = u.hasClass("bbaudio") ? b : (u.hasClass("bbvideo") ? k : a);
            var q = u.attr("data-url").replace(/&amp;/ig, "&");
            var p = u.attr("style");
            var s = p.indexOf("width") > -1 ? u.width() : 0;
            var w = p.indexOf("height") > -1 ? u.height() : 0;
            if (u.attr("data-width")) {
                s = u.attr("data-width")
            }
            if (u.attr("data-height")) {
                w = u.attr("data-height")
            }
            var t = u.attr("data-args");
            if (t && (t = n.trim(t).replace(/[\s,]+/g, ",").match(/^(\d+)?(?:[,x](\d+))?(?:(?:^|,)(audio|video))?/i))) {
                if (t[1] !== undefined) {
                    s = t[1]
                }
                if (t[2] !== undefined) {
                    w = t[2]
                }
                if (t[3] !== undefined && v == a) {
                    v = t[3]
                }
            }
            var r = l(q, s, w, v);
            if (!r) {
                e(u, v)
            } else {
                var x = n(r);
                s = x.attr("width");
                w = x.attr("height");
                u.css("width", s).css("height", w).empty().append(x)
            }
        })
    };
    var g = false;
    var d = function() {
        if (typeof jQuery == "undefined") {
            setTimeout(d, 200)
        } else {
            if (g) {
                jQuery.noConflict()
            }
            jQuery(c)
        }
    };
    if (typeof jQuery == "undefined") {
        g = true;
        var h = document.createElement("script");
        h.type = "text/javascript";
        h.src = "http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js";
        var j = document.getElementsByTagName("script")[0];
        j.parentNode.insertBefore(h, j)
    }
    d()
})();
