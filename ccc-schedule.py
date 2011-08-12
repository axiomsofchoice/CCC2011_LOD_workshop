"""Convert the Chaos Communication Camp 2011 Schedule from XML to RDF.
"""

import urllib

exampleDOI = 'http://dx.doi.org/10.1126/science.1157784'

op = urllib.FancyURLopener()
op.addheader('Accept', 'application/rdf+xml')
f = op.open(exampleDOI)
s = f.read()

#print s

from rdflib import Graph
from rdflib import Namespace
import StringIO

g = Graph()
g.parse(StringIO.StringIO(s), format="xml")

for s, p, o in g:
    if str(s)==exampleDOI and str(p)=="http://purl.org/dc/terms/title":
        print "%s has title \"%s\"" % (s,o)
