# coding=utf-8
import libvoikko

v = libvoikko.Voikko(u"fi")

f = open("./data/result.txt", "r")
txt = f.read()

word_list = txt.split(" ")
for w in word_list:
    orig = w
    w = w.lower().replace(".", "").replace(",", "")
    res = v.analyze(w)
    if res:
        sana = res[0]["BASEFORM"]
        muoto = res[0]["CLASS"]
        struct = res[0]["STRUCTURE"]
        print(orig + " " + struct + " " + muoto)
    else:
        print(orig)
