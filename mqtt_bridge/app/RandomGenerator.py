import string
import random

def RandomStr():
    characterList = ""

    # Adding letters to possible characters
    characterList += string.ascii_letters

    # Adding digits to possible characters
    characterList += string.digits

    randomStr = []

    for i in range(random.randrange(12,22)):

        # Picking a random character from our 
        # character list
        randomchar = random.choice(characterList)
        
        # appending a random character to password
        randomStr.append(randomchar)

    return ''.join(randomStr)


