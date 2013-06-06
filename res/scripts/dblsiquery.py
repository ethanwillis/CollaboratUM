from numpy import zeros
from numpy import mat
from numpy import dot
from numpy import *
from math import log
from numpy import asarray, sum
from numpy import zeros
#from scipy.linalg import svd
from string import maketrans 
from string import replace
from string import count
from time import clock, time
import getopt
import socket
import threading
import sys
from decimal import *
import psycopg2
import MySQLdb
from operator import itemgetter, attrgetter
import os
stopwords = ""
replacetable=''  
ignorechars =''
facultyarr=[]
class Faculty:
        def __init__(self, name,  matchingdegree):
            self.name = str(name)
           
            self.matchingdegree = float(matchingdegree)
        #def __repr__(self):
           # return repr((self.name, self.matchingdegree))
        def printfaculty(self):
            return self.name.ljust(15)+str(format(self.matchingdegree, 'f'))
class LSA(object):
    def __init__(self, stopwords, ignorechars,queries,cursor,cursor2):
        self.stopwords = stopwords
        self.ignorechars = ignorechars
        self.replacetable = replacetable
        self.wdict = {}
        self.queries=queries
        self.dcount = 0
        self.cursor=cursor
        self.cursor2=cursor2
    def parse(self):
        self.cursor.execute("SELECT * FROM dictionary;")
        linenum=1
        self.wordnum=int (self.cursor.rowcount)
        for i in self.cursor:
          
          
          
          
          
        
          
          #import pdb; pdb.set_trace()      
          for w in i:
            w1 = w.lower()      
            
            if not (w1 in self.wdict):
                
             self.wdict[w1] = [linenum]
          linenum=linenum+1 
  
                
    def build(self):
        #import pdb; pdb.set_trace()
        self.Facultyind=[]
        linenum=0
        self.cursor.execute("SELECT * FROM Doc_term_frequency_matrix;")
        
        self.A = zeros([len(self.wdict.keys()), self.facultynum])
        for i in self.cursor:
          colnum=0
          for j in i:           
           w1 = float(j)                  
           self.A[linenum,colnum] = w1 
            #import pdb; pdb.set_trace()        
           colnum=colnum+1
          linenum=linenum+1
        
             
       
    def readarr(self):
        linenum=0

       
        #self.U =zeros([self.wordnum, self.A.shape[1]])
        self.cursor.execute("SELECT * FROM U;")
        for i in self.cursor:
         if linenum==0:
          rowlength=len(i)
          self.U =zeros([self.wordnum,rowlength])
         colnum=0
         for j in i:
            w1 = float(j)      
            
            self.U[linenum,colnum] = w1 
           # import pdb; pdb.set_trace()        
            colnum=colnum+1
         linenum=linenum+1        

        linenum=0

       
        self.Vt =zeros([self.U.shape[1], self.U.shape[0]])
        self.cursor.execute("SELECT * FROM Vt;")
        for i in self.cursor:
          colnum=0
          for j in i:
            w1 = float(j)      
            
            self.Vt[linenum,colnum] = w1 
            #import pdb; pdb.set_trace()        
            colnum=colnum+1
          linenum=linenum+1
        linenum=0
        self.Sarr =[]
        self.cursor.execute("SELECT * FROM Sarr;")
        for i in self.cursor:
          colnum=0
          for j in i:
            w1 = float(j)      
            
            self.Sarr.append(w1)
            #import pdb; pdb.set_trace()        
            colnum=colnum+1
          linenum=linenum+1
        self.S = zeros( (self.U.shape[1], self.Vt.shape[0]) )
        
        #self.linum=len(s)
        
        #import pdb; pdb.set_trace()
        for i in range(0,len(self.Sarr)):
          self.S[i][i] = self.Sarr[i]
   
    def TFIDF(self):
        WordsPerDoc = sum(self.A, axis=0)        
        DocsPerWord = sum(asarray(self.A > 0, 'i'), axis=1)
        rows, cols = self.A.shape
        for i in range(rows):
            for j in range(cols):
                self.A[i,j] = (self.A[i,j] / WordsPerDoc[j]) * log(float(cols) / DocsPerWord[i])
    
       
    def queryA(self,s,t1):
        returnstr=""
        valid=False;
        #self.linum=len(s)
        self.linum=1
        
        Sj = dot(self.S,self.Vt)
        
      
        #import pdb; pdb.set_trace() 
       
        
        
        self.resultarr=zeros([self.linum,self.facultynum])
        self.validkeys=[False]* self.linum
        currlinum=0
        
        #for currlinum in range(0,len(self.queries)):
        for currlinum in range(0,1):
          #line2=self.queries[currlinum]
          #cosines = zeros((self.A.shape[1],1))
          self.vec = zeros((self.wordnum,1))
          #if not self.queries[currlinum]: break
          if not s: break
          #words = self.queries[currlinum].split();
          words = s.split();
         # import pdb; pdb.set_trace() 
          
          for w in words:
            w1 = w.lower()      
            
            if  (w1 in self.wdict):
               self.vec[self.wdict[w1][0]-1]=1.0
               self.validkeys[currlinum]=True;
          TU=self.U.transpose()  
          uq = dot(TU,self.vec)
	  uq_2 = linalg.norm(uq)
          for j in range(0,self.facultynum):
	   
	    s = Sj[:,j]
            p=0;
            #print "lentth of A" + str(len(s))   
            #f3=open("/home/dchu/query/teat.txt","w+")
            #for r in range(0,len(s)):
                
              #f3.write(str(s[r]))
              #f3.write("\n") 
            #f3.close()
            #import pdb; pdb.set_trace() 
	    s_2 = linalg.norm( s )
	    cosj = dot( s, uq ) / ( s_2 * uq_2 )
	    #cosines[j] = cosj   
            self.resultarr[currlinum,j]=("%.9f" %cosj)
        self.keptnum=0 
        self.keptarr=[]
        if  ',' in t1:
          typekeptarr=t1.split(',')
          #import pdb; pdb.set_trace()
          for j in typekeptarr:
             typekept=int(j)
             if typekept==2: 
               self.cursor2.execute("SELECT investigator_id FROM investigator WHERE type=%s ;",("investigator",))
             if typekept==1: 
               self.cursor2.execute("SELECT investigator_id FROM investigator WHERE type=%s ;",("grant",))
             if typekept==3: 
               self.cursor2.execute("SELECT investigator_id FROM investigator WHERE type=%s ;",("class",))
             if typekept==0: 
                self.cursor2.execute("SELECT investigator_id FROM investigator ;")
             self.keptnum=self.keptnum+int (self.cursor2.rowcount)
             for i in self.cursor2:
               self.keptarr.append(int(i[0]))  
            #import pdb; pdb.set_trace() 
        else:
          typekept=int(t1)
          
          if typekept==2: 
            self.cursor2.execute("SELECT investigator_id FROM investigator WHERE type=%s ;",("investigator",))
          if typekept==1: 
            self.cursor2.execute("SELECT investigator_id FROM investigator WHERE type=%s ;",("grant",))
          if typekept==3: 
            self.cursor2.execute("SELECT investigator_id FROM investigator WHERE type=%s ;",("class",))
          if typekept==0: 
            self.cursor2.execute("SELECT investigator_id FROM investigator ;")
          self.keptnum=int (self.cursor2.rowcount)
          for i in self.cursor2:
            self.keptarr.append(int(i[0]))
       # f4=open("/home/dchu/query/queryresults.txt","w+")
        for i in range(self.linum):
         if self.validkeys[i]>0:
                 
          #currlist=[Faculty]*self.keptnum
          currlist=[]
          currnum=0
         # f1=Faculty("asd",0.01)
         # print "f1"+f1.name
          #print "f1"+f1.matchingdegree+"\n"
          for j in range(self.facultynum):
           if int(self.facultyarr[j]) in self.keptarr:
            #print self.facultyarr[j]
            #import pdb; pdb.set_trace()
            #currlist[currnum]=Faculty(str(self.facultyarr[j]),float(self.resultarr[i,j]))
            currlist.append(Faculty(str(self.facultyarr[j]),float(self.resultarr[i,j])))
            currnum=currnum+1
            #print  "matching"+str(currlist[currnum].name)+"\n"
           # print  "matching"+str(d2.name)+"\n"
           #currlist[j]=Faculty("sdf",self.resultarr[i,j])
          print  currnum
          sortfaculty=sorted(currlist, key=lambda Faculty:Faculty.matchingdegree,reverse=True)
          for j in sortfaculty:
           #f4.write(j.printfaculty())
            returnstr=returnstr+j.printfaculty()+"\n"
           #f4.write('\n') 
         else: 
           returnstr= "no corresponding term in the dictionary"
#f4.write("no corresponding term in the dictionary")
          #f4.write('\n') 
        return returnstr  
       
    def printSVD(self):
        print 'Here are the singular values'
        print self.S
        print 'Here are the first 1 columns of the U matrix'
        print -1*self.U[:, 0:1]
        print 'Here are the first 1 rows of the Vt matrix'
        print -1*self.Vt[0:1, :]
    def printB(self):
        print 'Here is the count matrix'
        print self.A
    def getfaculty(self):
        self.facultyarr=[]
       # for j in self.Facultyind:
        self.cursor.execute("SELECT col0 FROM doc_pairwise_cosine_matrix;")
        self.facultynum=int (self.cursor.rowcount)
        for i in self.cursor:
             self.facultyarr.append(i[0])
def procthread(connection, lsiobj,addr): 
      locallsi=lsiobj  
      loop=0 
      content="" 
      while content!="ack":
        print str(addr)+"   working"
        content= connection.recv(1024)
        print "received   "+content
        if not content:break
        if  '|' in content:
          pack=content.split('|')
          
          resultstr=lsiobj.queryA(pack[1],pack[0])
          connection.sendall(resultstr+"\0")
          print "message sent"
      print str(addr)+"   quiting"    
  
displaytype=0       
opts, args=getopt.getopt(sys.argv[1:],"t:") 
db =  MySQLdb.connect("localhost","root","baseg","parsingdata")
cursor = db.cursor()
db2 =  MySQLdb.connect("localhost","root","baseg","collaboratum")
cursor2 = db2.cursor()

for o,p in opts:
  if o in ['-t','--type']:
     displaytype=p
#db2 =  MySQLdb.connect("localhost","root","123456","collaboratum")
#cursor2 = db2.cursor()
#print str(displaytype)
mylsa = LSA(stopwords, ignorechars,args,cursor,cursor2)
mylsa.getfaculty()
mylsa.parse()
#mylsa.build()
mylsa.readarr()
HOST = ''                 # Symbolic name meaning all available interfaces
PORT = 50005              # Arbitrary non-privileged port
s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
s.bind((HOST, PORT))
s.listen(5)
print "server start listenning"
#mylsa.writearr()
while 1:
 
 conn, addr = s.accept()
 print "accept connection from "+ str(addr)
 t=threading.Thread(target=procthread,args=(conn,mylsa,addr))
 t.start() 
s.close()  
db.close()

