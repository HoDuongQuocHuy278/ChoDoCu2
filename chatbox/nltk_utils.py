import nltk
from nltk.stem.porter import PorterStemmer
import numpy as np

stemmer = PorterStemmer()

# Tách câu thành từ
def tokenize(sentence):
    return nltk.word_tokenize(sentence)

# Đưa từ về dạng gốc (stem)
def stem(word):
    return stemmer.stem(word.lower())

# Tạo bag-of-words
def bag_of_words(tokenized_sentence, all_words):
    tokenized_sentence = [stem(w) for w in tokenized_sentence]

    bag = np.zeros(len(all_words), dtype=np.float32)
    for idx, w in enumerate(all_words):
        if w in tokenized_sentence:
            bag[idx] = 1

    return bag
