# coding=utf-8
import libvoikko

v = libvoikko.Voikko(u"fi")

f = open("./data/result.txt", "r")
txt = f.read().replace("\n", " ")

# replace some spoken finnish
with open("./resources/replaces.txt", "r") as myfile:
    for line in myfile:
        name, var = line.partition("=")[::2]
        txt = txt.replace(" " + name.strip() + " ", " " + var.strip() + " ")
        txt = txt.replace(" " + name.strip().capitalize() + " ", " " + var.strip().capitalize() + " ")

def good(w, orig):
  res = v.analyze(w)
  if res:
      sana = res[0]["BASEFORM"]
      muoto = res[0]["CLASS"]
      struct = res[0]["STRUCTURE"]
      print(orig + " " + sana + " " + muoto)
      return True
  return False

# analyze the words into log file
word_list = txt.split()
for w in word_list:
    orig = w
    w = w.lower().replace(".", "").replace(",", "").replace("?", "").replace("!", "")
    r = good(w, orig)
    if not r:
        print (orig)
