"""Convert the Chaos Communication Camp 2011 Schedule from XML to RDF.
"""

import urllib2
from lxml import etree
from StringIO import StringIO
from rdflib import Graph
from rdflib import RDF
from rdflib import RDFS
from rdflib import Namespace
from rdflib import URIRef
from rdflib import Literal

def read_xml():
    source_schedule = 'http://events.ccc.de/camp/2011/Fahrplan/schedule.en.xml'
    
    req = urllib2.Request(source_schedule)
    r = urllib2.urlopen(req)
    s = r.read()
    sio = StringIO(s)
    
    tree = etree.parse(sio)
    
    return tree

# Read in source XML data
rn = read_xml()

# Create the graph for output RDF/XML data
g = Graph()

# Declare some useful namespaces
CCCONTO = Namespace('http://events.ccc.de/schedule.owl#')
CCC = Namespace('http://fahrplan.u0d.de/')
CCCO = Namespace('http://fahrplan.u0d.de/schedule.owl#')


# Get overall conference metadata
myXpath = etree.ETXPath("/schedule/conference/title")
conference_title = myXpath(rn)[0].text

conf = URIRef("http://fahrplan.u0d.de/camp2011")
g.add((conf, RDFS.label, Literal(conference_title)))

# TODO: extract the following metadata
"""
<title>Chaos Communication Camp 2011</title>
<subtitle>Project Flow Control</subtitle>
<venue>Luftfahrtmuseum Finowfurt</venue>
<city>Finowfurt</city>
<start>2011-08-10</start>
<end>2011-08-14</end>
<days>5</days>
<release>Version 1.3</release>
<day_change>06:00</day_change>
<timeslot_duration>00:30</timeslot_duration>
"""

# Iterate over the days of the conference
daysXpath = etree.ETXPath("/schedule/day")
days = daysXpath(rn)
for day in days:
    print day
    
    # Iterate over rooms for that day
    for room in day.iterfind("./room"):
        print room.get("name")
        #
        ## Iterate over events for this room on this day
        for event in room.iterfind("./event"):
            print event.get("id")
            event_slug = event.find("slug").text
            if event_slug is not None:
                eventURI = CCC[event.find("slug").text]
                g.add((conf, CCCO.hasEvent, eventURI))
                g.add((eventURI, RDFS.label, Literal(event.find("title").text)))
                g.add((eventURI, RDF.type, CCCO.Event))
            else:
                continue
                # TODO: something here
            
            # Iterate over persons
            # Iterate over links

g.serialize("data/ccc.rdf")

